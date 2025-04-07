<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\EmailVerification;

class EmailVerificationController extends Controller
{
    /**
     * Resend the email verification to a specific user.
     */
    public function resend(User $user)
    {
        Mail::to($user->email)->send(new EmailVerification($user));

        return $this->successResponse('Verification email resent successfully.');
    }

    /**
     * Verify the user's email using a secret code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = User::where('verification_code', $request->code)->first();

        if (!$user) {
            return $this->errorResponse('Invalid verification code.', null, 400);
        }

        $user->email_verified_at = now();
        $user->verification_code = null; // Clear the verification code after successful verification
        $user->save();

        return $this->successResponse('Email verified successfully.');
    }
}
