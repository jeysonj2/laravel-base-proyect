<?php

namespace App\Http\Controllers;

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
     * @OA\Post(
     *     path="/api/password/email",
     *     summary="Send password reset link",
     *     description="Initiates the password reset flow by sending a reset link to the specified email address.
     *     This endpoint is the first step in the password reset process. When a user forgets their password,
     *     they request a reset link through this endpoint. The system generates a unique, time-limited token
     *     (expiring after PASSWORD_RESET_TOKEN_EXPIRY_MINUTES, default 60 minutes) and sends it to the user's email.
     *     The user then uses this token along with their email in the password reset endpoint to set a new password.",
     *     operationId="sendPasswordResetLink",
     *     tags={"Password Reset"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email"},
     *
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="user@example.com",
     *                 description="Email address of the registered user who needs to reset their password"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Password reset link sent to your email")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The email field must be a valid email.")
     *         )
     *     )
     * )
     *
     * @param Request $request Request containing the user's email
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
     * @OA\Post(
     *     path="/api/password/reset",
     *     summary="Reset password",
     *     description="Completes the password reset process using the token received in email. This endpoint is the
     *     second and final step in the password reset flow. After receiving the reset token via email, the user
     *     submits their email, token, and desired new password. The system validates that the token matches the one
     *     stored for the user and that it hasn't expired. If valid, the user's password is updated, the token is cleared,
     *     and a confirmation email is sent to notify the user of the successful password change. The new password must
     *     meet the strong password requirements.",
     *     operationId="resetPassword",
     *     tags={"Password Reset"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email", "token", "password"},
     *
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="user@example.com",
     *                 description="Email address of the user resetting their password"
     *             ),
     *             @OA\Property(
     *                 property="token",
     *                 type="string",
     *                 example="60charsstringtoken",
     *                 description="Token received in the password reset email (60 characters)"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="NewStrongPassword123!",
     *                 description="New password (must meet strong password requirements: upper/lowercase, number, special char)"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Password has been reset successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or invalid token",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Invalid or expired password reset token")
     *         )
     *     )
     * )
     *
     * @param Request $request Request containing token, email, and new password
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

        if (! $user) {
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
