<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Support\ChildProfileAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ChildProfileController extends Controller
{
    /**
     * Get all child profiles for authenticated parent
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $profiles = ChildProfileAccess::queryFor($user)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($profiles);
    }

    /**
     * Create a new child profile
     * 
     * When a parent creates a child profile, we also create a User account
     * for the child with the 'child' role so they can login independently.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'avatar' => 'nullable|string|max:10',
            'pin' => 'required|digits:4', // Exactly 4 digits
            'tribe_preferences' => 'nullable|array',
            'tribe_preferences.*' => 'exists:tribes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $parent = $request->user();

        // Generate unique email for child (using parent's email as base)
        $childEmail = $this->generateChildEmail($parent->email, $request->name);

        // Create User account for the child
        $childUser = \App\Models\User::create([
            'name' => $request->name,
            'email' => $childEmail,
            'password' => \Hash::make($request->pin), // Store PIN as password
            'organisation_id' => $parent->organisation_id, // Inherit parent's org if any
            'email_verified_at' => now(), // Auto-verify child accounts
        ]);

        // Assign 'child' role
        $childUser->assignRole('child');

        // Create child profile linked to both parent and child user
        $profile = ChildProfile::create([
            'user_id' => $parent->id, // Parent who created this profile
            'child_user_id' => $childUser->id, // Child's own user account
            'name' => $request->name,
            'avatar' => $request->avatar,
            'dob' => $request->date_of_birth,
            'total_stars' => 0,
        ]);

        // Refresh to get computed age_band
        $profile->refresh();

        return response()->json([
            'profile' => $profile,
            'child_email' => $childEmail,
            'message' => 'Child profile created successfully. Child can now login with their credentials.',
        ], 201);
    }

    /**
     * Generate a unique email for a child based on parent's email
     */
    private function generateChildEmail(string $parentEmail, string $childName): string
    {
        // Extract parent email parts
        $parts = explode('@', $parentEmail);
        $username = $parts[0];
        $domain = $parts[1];

        // Create child-specific username
        $childSlug = \Str::slug($childName);
        $baseEmail = "{$username}.{$childSlug}@{$domain}";

        // Ensure uniqueness
        $email = $baseEmail;
        $counter = 1;
        while (\App\Models\User::where('email', $email)->exists()) {
            $email = "{$username}.{$childSlug}{$counter}@{$domain}";
            $counter++;
        }

        return $email;
    }

    /**
     * Get a specific child profile
     */
    public function show(Request $request, $id)
    {
        $profile = ChildProfileAccess::findForUserOrFail($request->user(), (int) $id);

        return response()->json($profile);
    }

    /**
     * Update a child profile
     */
    public function update(Request $request, $id)
    {
        $profile = ChildProfileAccess::findForUserOrFail($request->user(), (int) $id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date|before:today',
            'avatar' => 'nullable|string|max:10',
            'tribe_preferences' => 'nullable|array',
            'tribe_preferences.*' => 'exists:tribes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [];
        if ($request->has('name')) $data['name'] = $request->name;
        if ($request->has('date_of_birth')) $data['dob'] = $request->date_of_birth;
        
        $profile->update($data);

        return response()->json($profile);
    }

    /**
     * Delete a child profile
     * 
     * Also deletes the child's User account
     */
    public function destroy(Request $request, $id)
    {
        $profile = ChildProfileAccess::findForUserOrFail($request->user(), (int) $id);

        // Only parents who own the profile may delete (not child login accounts).
        if ((int) $profile->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Only the parent account can delete this profile.'], 403);
        }

        // Delete the child's user account if it exists
        if ($profile->child_user_id) {
            $childUser = \App\Models\User::find($profile->child_user_id);
            if ($childUser) {
                $childUser->delete();
            }
        }

        // Delete the profile (cascade will handle progress events)
        $profile->delete();

        return response()->json(['message' => 'Profile deleted successfully']);
    }
}
