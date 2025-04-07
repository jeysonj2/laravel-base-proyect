<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponseTrait;
use App\Mail\PasswordChanged;
use App\Mail\PasswordReset as PasswordResetMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Password Reset Controller.
 * 
 * Handles the password reset process, including sending reset tokens
 * via email and processing password reset requests.
 */
class PasswordResetController extends Controller
{
    /**
     * Send a password reset token to the user's email.
     * 
     * Generates a unique token and sends it to the user's registered
     * email address with instructions to reset their password.
     * The token expires after a configurable time period.
     *
     * @param  \Illuminate\Http\Request  $request  Request containing the user's email
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Generate a unique token
        $token = Str::random(60);
        
        // Get token expiry time from environment variables (default to 60 minutes)
        $expiryMinutes = (int) env('PASSWORD_RESET_TOKEN_EXPIRY_MINUTES', 60);
        
        // Store the token in the database with the configured expiration time
        $user->password_reset_token = $token;
        $user->password_reset_expires_at = Carbon::now()->addMinutes($expiryMinutes);
        $user->save();

        // Send the email with the token
        Mail::to($user->email)->send(new PasswordResetMail($user, $token));

        return $this->successResponse('Password reset link sent to your email');
    }

    /**
     * Reset the password with the provided token.
     * 
     * Validates the token and email combination, ensures the token
     * has not expired, and then updates the user's password.
     * Sends a confirmation email after successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request  Request containing token, email, and new password
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|strong_password',
        ]);

        $user = User::where('email', $request->email)
            ->where('password_reset_token', $request->token)
            ->where('password_reset_expires_at', '>', Carbon::now())
            ->first();

        if (!$user) {
            return $this->validationErrorResponse('Invalid or expired password reset token');
        }

        // Reset the password
        $user->password = bcrypt($request->password);
        $user->password_reset_token = null;
        $user->password_reset_expires_at = null;
        $user->save();

        // Send confirmation email
        Mail::to($user->email)->send(new PasswordChanged($user));

        return $this->successResponse('Password has been reset successfully');
    }
}
