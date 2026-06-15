<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Jobs\SendUserNotificationJob;
use App\Models\User;
use App\Models\UserNotification;

class AuthController extends Controller
{
    /**
     * Register a new Mobile App User (Parent)
     * 
     * IMPORTANT: Mobile app registration is PARENT-ONLY.
     * - Children cannot register themselves
     * - Children belong to either:
     *   1. A parent (B2C model) - parent creates child profiles
     *   2. An organization (B2B model) - organization/teacher creates child profiles
     * - This endpoint automatically assigns the 'parent' role
     * - After registration, parents must verify their email with a code
     * - Verification code is sent via email and expires in 15 minutes
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // By default, self-registered mobile users do not belong to an organisation (B2C)
            'organisation_id' => null,
            // Email not verified yet - will be verified with code
            'email_verified_at' => null,
        ]);

        // Assign the 'parent' role (lowercase to match RoleSeeder)
        $user->assignRole('parent');

        // Generate verification code
        $verificationCode = VerificationCode::createForUser($user);

        // Send verification email asynchronously (queued)
        try {
            Mail::to($user->email)->queue(new VerificationCodeMail($user, $verificationCode));
        } catch (\Exception $e) {
            // Log error but don't fail registration
            \Log::error('Failed to queue verification email: ' . $e->getMessage());
        }

        // Generate Sanctum token (user can login but features limited until verified)
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please check your email for verification code.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'organization_id' => $user->organisation_id,
                'email_verified' => false,
            ],
            'token' => $token,
            'requires_verification' => true,
        ], 201);
    }

    /**
     * Login User and return a token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate new token
        $token = $user->createToken('mobile-app-token')->plainTextToken;

        if ($user->hasRole('parent')) {
            SendUserNotificationJob::dispatch(
                userId: $user->id,
                type: UserNotification::TYPE_LOGIN_ALERT,
                title: 'Welcome back',
                body: "You're signed in to {$user->name}'s CultureKids account.",
                data: [
                    'device' => $request->input('device_name'),
                ]
            );
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'organization_id' => $user->organisation_id,
            ],
            'token' => $token,
        ], 200);
    }

    /**
     * Get the authenticated user's profile
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'organization_id' => $user->organisation_id,
            ],
        ], 200);
    }

    /**
     * Logout and revoke the current token
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request...
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    /**
     * Verify email with code
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Check if already verified
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified',
                'verified' => true,
            ], 200);
        }

        // Find valid verification code
        $verificationCode = VerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('is_used', false)
            ->first();

        if (!$verificationCode) {
            return response()->json([
                'message' => 'Invalid verification code',
            ], 422);
        }

        if (!$verificationCode->isValid()) {
            return response()->json([
                'message' => 'Verification code has expired',
            ], 422);
        }

        // Mark code as used
        $verificationCode->markAsUsed();

        // Verify user email
        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'message' => 'Email verified successfully',
            'verified' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'organization_id' => $user->organisation_id,
                'email_verified' => true,
            ],
        ], 200);
    }

    /**
     * Resend verification code
     */
    public function resendVerificationCode(Request $request)
    {
        $user = $request->user();

        // Check if already verified
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified',
            ], 200);
        }

        // Generate new verification code
        $verificationCode = VerificationCode::createForUser($user);

        // Send verification email asynchronously (queued)
        try {
            Mail::to($user->email)->queue(new VerificationCodeMail($user, $verificationCode));
            
            return response()->json([
                'message' => 'Verification code sent successfully',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to queue verification email: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to send verification code. Please try again.',
            ], 500);
        }
    }
}
