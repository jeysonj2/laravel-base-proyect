<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
     * @OA\Post(
     *     path="/api/users/{user}/resend-verification",
     *     summary="Resend verification email",
     *     description="Resends the verification email to a specific user. This endpoint is used when the user has not
     *     received or has lost the initial verification email. It generates a unique verification code and sends
     *     an email with a link containing this code. The user then clicks on the link or manually enters the code
     *     in the verification endpoint to complete the email verification process.",
     *     operationId="resendVerification",
     *     tags={"Email Verification"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Verification email sent successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Verification email resent successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="User already verified",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="User is already verified.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     *
     * @param User $user The user to send the verification email to
     *
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
        if (! $user->verification_code) {
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
     * @OA\Get(
     *     path="/api/verify-email",
     *     summary="Verify email address",
     *     description="Verifies a user's email address using the verification code sent in the email. This endpoint
     *     completes the email verification flow. When a user is created or changes their email, they receive an email
     *     with a unique verification code. The user clicks on the verification link or manually enters the code in this
     *     endpoint. If the code matches what's stored in the database, the user's email is marked as verified and the
     *     verification code is cleared from the database.",
     *     operationId="verifyEmail",
     *     tags={"Email Verification"},
     *
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="Verification code sent to user's email (32-character hexadecimal string)",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Email verified successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid verification code",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Invalid verification code.")
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
     *             @OA\Property(property="message", type="string", example="The code field is required.")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = User::where('verification_code', $request->code)->first();

        if (! $user) {
            return $this->errorResponse('Invalid verification code.', null, 400);
        }

        $user->email_verified_at = now();
        $user->verification_code = null; // Clear the verification code after successful verification
        $user->save();

        return $this->successResponse('Email verified successfully.');
    }

    /**
     * Verify the user's email using a secret code through a web interface.
     *
     * This method provides a web interface for email verification, displaying
     * a success or error page based on the verification result.
     *
     * @OA\Get(
     *     path="/verify-email",
     *     summary="Verify email address (web interface)",
     *     description="Verifies a user's email address using the verification code sent in the email.
     *     This endpoint provides a web-based interface for email verification,
     *     displaying a success page or error page based on the verification result.",
     *     operationId="verifyEmailWeb",
     *     tags={"Email Verification"},
     *
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="Verification code sent to user's email (32-character hexadecimal string)",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Returns a web page with verification success or error message"
     *     )
     * )
     *
     * @return \Illuminate\View\View
     */
    public function verifyWeb(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = User::where('verification_code', $request->code)->first();

        if (! $user) {
            return view('auth.verification-error', [
                'message' => 'Invalid verification code.',
            ]);
        }

        $user->email_verified_at = now();
        $user->verification_code = null; // Clear the verification code after successful verification
        $user->save();

        return view('auth.verification-success', [
            'user' => $user,
        ]);
    }
}
