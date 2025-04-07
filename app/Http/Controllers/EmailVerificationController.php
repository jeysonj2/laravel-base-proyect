<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\EmailVerification;

/**
 * Email Verification Controller.
 * 
 * Handles the process of verifying user email addresses and resending
 * verification emails when needed.
 */
class EmailVerificationController extends Controller
{
    /**
     * Resend the email verification to a specific user.
     * 
     * Generates a new verification code if needed and sends a verification
     * email to the user's email address.
     *
     * @param  \App\Models\User  $user  The user to send the verification email to
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(User $user)
    {
        // Check if the user is already verified
        if ($user->email_verified_at) {
            return $this->errorResponse('User is already verified.', null, 400);
        }

        // Check if the user has a verification code
        // If not, generate a new one
        if (!$user->verification_code) {
            $user->verification_code = bin2hex(random_bytes(16));
            $user->save();
        }

        // Send the verification email
        Mail::to($user->email)->send(new EmailVerification($user));

        return $this->successResponse('Verification email resent successfully.');
    }

    /**
     * Verify the user's email using a secret code.
     * 
     * Validates the provided verification code against the database
     * and marks the user's email as verified if the code is valid.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
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
