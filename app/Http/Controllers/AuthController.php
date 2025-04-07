<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Hash;
use App\Mail\PasswordChanged;
use App\Mail\EmailVerification;
use App\Mail\AccountLocked;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Find the user without checking the password
        $user = User::where('email', $credentials['email'])->first();

        // If user doesn't exist, return invalid credentials message
        if (!$user) {
            return $this->unauthorizedResponse('Invalid credentials.');
        }

        // Check if the user is locked out
        if ($user->isLockedOut()) {
            if ($user->is_permanently_locked) {
                return $this->unauthorizedResponse('Your account has been permanently locked due to multiple failed login attempts. Please contact an administrator.');
            } else {
                $minutesRemaining = now()->diffInMinutes($user->locked_until);
                return $this->unauthorizedResponse("Your account is temporarily locked due to multiple failed login attempts. Please try again in {$minutesRemaining} minutes or contact an administrator.");
            }
        }

        // Attempt authentication
        if (!$token = Auth::guard('api')->attempt($credentials)) {
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
                    return $this->unauthorizedResponse('Your account has been permanently locked due to multiple failed login attempts. Please contact an administrator.');
                } else {
                    return $this->unauthorizedResponse("Your account has been temporarily locked due to multiple failed login attempts. Please try again in {$lockoutDuration} minutes or contact an administrator.");
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
            'ttl' => $refreshTTL
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        try {
            $refreshToken = $request->header('Authorization');
            $refreshToken = str_replace('Bearer ', '', $refreshToken);

            if (!$refreshToken) {
                return $this->unauthorizedResponse('Refresh token is required.');
            }

            // Set the refresh token
            JWTAuth::setToken($refreshToken);

            // Verify the token is valid, not expired, and has the refresh claim
            $payload = JWTAuth::getPayload();
            if (!$payload->get('refresh')) {
                $errorData = config('app.debug') ? ['exception-message' => "Token does not have refresh claim."] : null;
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
        } catch (TokenInvalidException | TokenExpiredException $e) {
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
     * @param  \Illuminate\Http\Request  $request
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
        if (!Hash::check($validated['current_password'], $user->password)) {
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Check if password is being attempted to change
        if ($request->has('password')) {
            return $this->validationErrorResponse('Password cannot be updated through this endpoint. Please use the change-password endpoint instead.');
        }

        // Check if role is being attempted to change
        if ($request->has('role_id')) {
            return $this->validationErrorResponse('Role cannot be updated through this endpoint.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'last_name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|case_insensitive_unique:users,email,' . $user->id,
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
