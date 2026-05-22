<?php

namespace App\Actions\Auth;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailVerificationCode
{
    public function send(User $user): VerificationCode
    {
        $verificationCode = VerificationCode::createForUser($user);

        try {
            Mail::to($user->email)->send(new VerificationCodeMail($user, $verificationCode));
        } catch (\Throwable $e) {
            Log::error('Failed to send verification code email', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $verificationCode;
    }
}
