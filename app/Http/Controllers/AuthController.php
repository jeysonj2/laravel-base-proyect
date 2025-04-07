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

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Find the user without checking the password
        $user = User::where('email', $credentials['email'])->first();

        // If user doesn't exist, return invalid credentials message
        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Check if the user is locked out
        if ($user->isLockedOut()) {
            if ($user->is_permanently_locked) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Your account has been permanently locked due to multiple failed login attempts. Please contact an administrator.',
                ], 401);
            } else {
                $minutesRemaining = now()->diffInMinutes($user->locked_until);
                return response()->json([
                    'code' => 401,
                    'message' => "Your account is temporarily locked due to multiple failed login attempts. Please try again in {$minutesRemaining} minutes or contact an administrator.",
                ], 401);
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
                    return response()->json([
                        'code' => 401,
                        'message' => 'Your account has been permanently locked due to multiple failed login attempts. Please contact an administrator.',
                    ], 401);
                } else {
                    return response()->json([
                        'code' => 401,
                        'message' => "Your account has been temporarily locked due to multiple failed login attempts. Please try again in {$lockoutDuration} minutes or contact an administrator.",
                    ], 401);
                }
            }

            return response()->json([
                'code' => 401,
                'message' => 'Invalid credentials.',
            ], 401);
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

        return response()->json([
            'code' => 200,
            'message' => 'Login successful.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
                'refresh_token' => $refreshToken,
                'refresh_expires_in' => $refreshTTL * 60,
            ],
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $refreshToken = $request->header('Authorization');
            $refreshToken = str_replace('Bearer ', '', $refreshToken);

            if (!$refreshToken) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Refresh token is required.',
                ], 401);
            }

            // Set the refresh token
            JWTAuth::setToken($refreshToken);

            // Verify the token is valid, not expired, and has the refresh claim
            $payload = JWTAuth::getPayload();
            if (!$payload->get('refresh')) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Invalid refresh token.',
                    'data' => config('app.debug') ? [
                        'exception-message' => "Token does not have refresh claim.",
                    ] : null,
                ], 401);
            }

            // If we've reached here, token is valid, not expired, and has refresh claim
            $user = JWTAuth::authenticate();

            // Generate only a new access token
            $newToken = JWTAuth::fromUser($user);

            return response()->json([
                'code' => 200,
                'message' => 'Token refreshed successfully.',
                'data' => [
                    'access_token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
                ],
            ]);
        } catch (TokenInvalidException | TokenExpiredException $e) {
            return response()->json([
                'code' => 401,
                'message' => 'Invalid refresh token.',
                'data' => config('app.debug') ? [
                    'exception-message' => $e->getMessage(),
                ] : null,
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Could not refresh token.',
                'data' => config('app.debug') ? [
                    'exception-message' => $e->getMessage(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            // Invalidate the token by adding it to the blacklist
            Auth::guard('api')->logout();

            return response()->json([
                'code' => 200,
                'message' => 'Successfully logged out',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to logout, please try again.',
                'data' => config('app.debug') ? [
                    'exception-message' => $e->getMessage(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Change the authenticated user's password.
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
            return response()->json([
                'code' => 422,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        // Update the password
        $user->password = bcrypt($validated['new_password']);
        $user->save();

        // Send confirmation email
        Mail::to($user->email)->send(new PasswordChanged($user));

        return response()->json([
            'code' => 200,
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Get the authenticated user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = Auth::guard('api')->user();
        
        return response()->json([
            'code' => 200,
            'message' => 'Profile retrieved successfully.',
            'data' => $user,
        ]);
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Check if password is being attempted to change
        if ($request->has('password')) {
            return response()->json([
                'code' => 422,
                'message' => 'Password cannot be updated through this endpoint. Please use the change-password endpoint instead.',
            ], 422);
        }

        // Check if role is being attempted to change
        if ($request->has('role_id')) {
            return response()->json([
                'code' => 422,
                'message' => 'Role cannot be updated through this endpoint.',
            ], 422);
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
            
            return response()->json([
                'code' => 200,
                'message' => 'Profile updated successfully. Please verify your new email address.',
                'data' => $user,
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Profile updated successfully.',
            'data' => $user,
        ]);
    }
}
