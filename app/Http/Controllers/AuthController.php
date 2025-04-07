<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'code' => 401,
                'message' => 'Invalid credentials.',
            ], 401);
        }

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
}
