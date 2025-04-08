<?php

namespace App\Http\Controllers;

use App\Mail\AccountLocked;
use App\Mail\EmailVerification;
use App\Mail\PasswordChanged;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Authentication Controller.
 *
 * Handles user authentication, token management, password management,
 * and user profile operations for the API.
 */
class AuthController extends Controller
{
    /**
     * Authenticate a user and generate access and refresh tokens.
     *
     * This method also handles account lockout logic when invalid credentials
     * are provided multiple times.
     *
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticate user and generate tokens",
     *     description="Logs in a user with email and password, and returns access and refresh tokens.
     *     This endpoint implements a sophisticated account lockout mechanism to prevent brute-force attacks:
     *     after MAX_LOGIN_ATTEMPTS (default: 3) failed attempts within LOGIN_ATTEMPTS_WINDOW_MINUTES (default: 5),
     *     the account is temporarily locked for ACCOUNT_LOCKOUT_DURATION_MINUTES (default: 60).
     *     If a user gets locked out MAX_LOCKOUTS_IN_PERIOD (default: 2) times within
     *     LOCKOUT_PERIOD_HOURS (default: 24), their account becomes permanently locked and requires
     *     administrator intervention to unlock. When an account is locked, a notification email is sent to the user.",
     *     operationId="authLogin",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password"},
     *
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="user@example.com",
     *                 description="User's registered email address (case-insensitive)"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="password123",
     *                 description="User's password"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Login successful."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="access_token",
     *                     type="string",
     *                     example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *                     description="JWT token for authentication (short-lived)"
     *                 ),
     *                 @OA\Property(
     *                     property="token_type",
     *                     type="string",
     *                     example="bearer",
     *                     description="Type of token, always 'bearer'"
     *                 ),
     *                 @OA\Property(
     *                     property="expires_in",
     *                     type="integer",
     *                     example=3600,
     *                     description="Access token expiration time in seconds"
     *                 ),
     *                 @OA\Property(
     *                     property="refresh_token",
     *                     type="string",
     *                     example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *                     description="JWT refresh token (long-lived) for obtaining a new access token"
     *                 ),
     *                 @OA\Property(
     *                     property="refresh_expires_in",
     *                     type="integer",
     *                     example=1209600,
     *                     description="Refresh token expiration time in seconds"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid credentials or account locked",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Invalid credentials.")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Find the user without checking the password
        $user = User::where('email', $credentials['email'])->first();

        // If user doesn't exist, return invalid credentials message
        if (! $user) {
            return $this->unauthorizedResponse('Invalid credentials.');
        }

        // Check if the user is locked out
        if ($user->isLockedOut()) {
            if ($user->is_permanently_locked) {
                return $this->unauthorizedResponse(
                    'Your account has been permanently locked due to multiple failed login attempts. ' .
                    'Please contact an administrator.'
                );
            } else {
                $minutesRemaining = now()->diffInMinutes($user->locked_until);

                return $this->unauthorizedResponse(
                    'Your account is temporarily locked due to multiple failed login attempts. ' .
                    "Please try again in {$minutesRemaining} minutes or contact an administrator."
                );
            }
        }

        // Attempt authentication
        if (! $token = Auth::guard('api')->attempt($credentials)) {
            // Authentication failed, increment failed attempts
            $wasLocked = $user->registerFailedLoginAttempt();

            // If user got locked, send notification email
            if ($wasLocked) {
                $lockoutDuration = (int) env('ACCOUNT_LOCKOUT_DURATION_MINUTES', 60);
                Mail::to($user->email)->send(new AccountLocked(
                    $user,
                    $user->is_permanently_locked,
                    $user->is_permanently_locked ? null : $lockoutDuration
                ));

                if ($user->is_permanently_locked) {
                    return $this->unauthorizedResponse(
                        'Your account has been permanently locked due to multiple failed login attempts. ' .
                        'Please contact an administrator.'
                    );
                } else {
                    return $this->unauthorizedResponse(
                        'Your account has been temporarily locked due to multiple failed login attempts. ' .
                        "Please try again in {$lockoutDuration} minutes or contact an administrator."
                    );
                }
            }

            return $this->unauthorizedResponse('Invalid credentials.');
        }

        // Authentication successful, reset failed attempts counter
        $user->resetFailedLoginAttempts();

        // Configure JWTAuth factory to use the refresh TTL
        $refreshTTL = config('jwt.refresh_ttl');

        // Create a refresh token with the specified TTL
        $factory = JWTAuth::factory();
        $originalTTL = $factory->getTTL(); // Store original TTL
        $factory->setTTL($refreshTTL); // Set refresh TTL

        $refreshToken = JWTAuth::claims([
            'refresh' => true,
            'ttl' => $refreshTTL,
        ])->fromUser(Auth::guard('api')->user());

        // Restore original TTL for future tokens
        $factory->setTTL($originalTTL);

        return $this->successResponse('Login successful.', [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'refresh_token' => $refreshToken,
            'refresh_expires_in' => $refreshTTL * 60,
        ]);
    }

    /**
     * Refresh a user's access token using their refresh token.
     *
     * Validates the refresh token and generates a new access token if valid.
     *
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh access token",
     *     description="Generate a new access token using a valid refresh token. This endpoint is used when the
     *     short-lived access token expires but the refresh token is still valid. The refresh token must include
     *     a special 'refresh' claim to be valid for this operation. This endpoint is important for maintaining
     *     user sessions without requiring frequent re-authentication while still providing security through
     *     the short-lived nature of the access tokens.",
     *     operationId="authRefresh",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="access_token",
     *                     type="string",
     *                     example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *                     description="New JWT access token"
     *                 ),
     *                 @OA\Property(
     *                     property="token_type",
     *                     type="string",
     *                     example="bearer",
     *                     description="Type of token, always 'bearer'"
     *                 ),
     *                 @OA\Property(
     *                     property="expires_in",
     *                     type="integer",
     *                     example=3600,
     *                     description="Access token expiration time in seconds"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid refresh token",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Invalid refresh token.")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        try {
            $refreshToken = $request->header('Authorization');
            $refreshToken = str_replace('Bearer ', '', $refreshToken);

            if (! $refreshToken) {
                return $this->unauthorizedResponse('Refresh token is required.');
            }

            // Set the refresh token
            JWTAuth::setToken($refreshToken);

            // Verify the token is valid, not expired, and has the refresh claim
            $payload = JWTAuth::getPayload();
            if (! $payload->get('refresh')) {
                $errorData = config('app.debug')
                    ? ['exception-message' => 'Token does not have refresh claim.']
                    : null;

                return $this->unauthorizedResponse('Invalid refresh token.', $errorData);
            }

            // If we've reached here, token is valid, not expired, and has refresh claim
            $user = JWTAuth::authenticate();

            // Generate only a new access token
            $newToken = JWTAuth::fromUser($user);

            return $this->successResponse('Token refreshed successfully.', [
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            ]);
        } catch (TokenInvalidException|TokenExpiredException $e) {
            $errorData = config('app.debug') ? ['exception-message' => $e->getMessage()] : null;

            return $this->unauthorizedResponse('Invalid refresh token.', $errorData);
        } catch (JWTException $e) {
            $errorData = config('app.debug') ? ['exception-message' => $e->getMessage()] : null;

            return $this->serverErrorResponse('Could not refresh token.', $errorData);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * Adds the current token to the blacklist to prevent further use.
     *
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     description="Invalidate the user's token and log them out",
     *     operationId="authLogout",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error during logout",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to logout, please try again.")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            // Invalidate the token by adding it to the blacklist
            Auth::guard('api')->logout();

            return $this->successResponse('Successfully logged out');
        } catch (JWTException $e) {
            $errorData = config('app.debug') ? ['exception-message' => $e->getMessage()] : null;

            return $this->serverErrorResponse('Failed to logout, please try again.', $errorData);
        }
    }

    /**
     * Change the authenticated user's password.
     *
     * Validates the current password and enforces strong password requirements
     * for the new password. Sends a confirmation email upon successful change.
     *
     * @OA\Post(
     *     path="/api/change-password",
     *     summary="Change user password",
     *     description="Change the authenticated user's password with validation",
     *     operationId="changePassword",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"current_password","new_password"},
     *
     *             @OA\Property(property="current_password", type="string", format="password", example="oldPass123"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newStrongPass123!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Password changed successfully.")
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
     *             @OA\Property(property="message", type="string", example="Current password is incorrect.")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $user = Auth::guard('api')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|strong_password|different:current_password',
        ]);

        // Verify the current password
        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->validationErrorResponse('Current password is incorrect.');
        }

        // Update the password
        $user->password = bcrypt($validated['new_password']);
        $user->save();

        // Send confirmation email
        Mail::to($user->email)->send(new PasswordChanged($user));

        return $this->successResponse('Password changed successfully.');
    }

    /**
     * Get the authenticated user's profile.
     *
     * Returns the current user's profile information.
     *
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Get user profile",
     *     description="Retrieve the authenticated user's profile information",
     *     operationId="getProfile",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Profile retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = Auth::guard('api')->user();

        return $this->successResponse('Profile retrieved successfully.', $user);
    }

    /**
     * Update the authenticated user's profile.
     *
     * Allows users to update their own profile information with restrictions:
     * - Cannot change password through this endpoint
     * - Cannot change role through this endpoint
     * - Email changes trigger a new verification process
     *
     * @OA\Put(
     *     path="/api/profile",
     *     summary="Update user profile",
     *     description="Update the authenticated user's profile information",
     *     operationId="updateProfile",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="john.doe@example.com"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/User"
     *             )
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
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The email field must be a valid email address."
     *             )
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Check if password is being attempted to change
        if ($request->has('password')) {
            return $this->validationErrorResponse(
                'Password cannot be updated through this endpoint. Please use the change-password endpoint instead.'
            );
        }

        // Check if role is being attempted to change
        if ($request->has('role_id')) {
            return $this->validationErrorResponse('Role cannot be updated through this endpoint.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'last_name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|case_insensitive_unique:users,email,' .
                $user->id,
        ]);

        $currentEmail = $user->email;

        $user->update($validated);

        // If email has changed, reset verification status and send verification email
        if (isset($validated['email']) && $validated['email'] !== $currentEmail) {
            $user->verification_code = bin2hex(random_bytes(16));
            $user->email_verified_at = null;
            $user->save();

            // Send verification email
            Mail::to($validated['email'])->send(new EmailVerification($user));

            return $this->successResponse('Profile updated successfully. Please verify your new email address.', $user);
        }

        return $this->successResponse('Profile updated successfully.', $user);
    }
}
