<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

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

        return response()->json([
            'code' => 200,
            'message' => 'Login successful.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
                'refresh_token' => JWTAuth::claims(['refresh' => true])->fromUser(Auth::guard('api')->user()),
            ],
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $refreshToken = $request->header('Authorization');
            $refreshToken = str_replace('Bearer ', '', $refreshToken);

            if (!$refreshToken || !JWTAuth::setToken($refreshToken)->getClaim('refresh')) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Invalid refresh token.',
                ], 401);
            }

            $newToken = Auth::guard('api')->refresh();

            return response()->json([
                'code' => 200,
                'message' => 'Token refreshed successfully.',
                'data' => [
                    'access_token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
                ],
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Could not refresh token.',
                'data' => [
                    'eMessage' => $e->getMessage(),
                ]
            ], 500);
        }
    }
}
