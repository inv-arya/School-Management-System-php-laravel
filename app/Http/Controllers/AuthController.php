<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;


use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException; 
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException; 

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|exists:users,username',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('username', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Invalid credentials',
                ], 401);
            }

            $user = JWTAuth::user();

           
            $refreshToken = $this->createRefreshToken($user);

            
            return response()->json([
                'username'      => $user->username,
                'role'          => $user->role,
                'access'  => $token,
                'refresh' => $refreshToken, 
                
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Could not create token',
            ], 500); 
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Logout failed',
            ], 500);
        }
    }

    
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string'
        ]);

        try {
            $payload = JWTAuth::setToken($request->refresh_token)->getPayload();

            
            if ($payload->get('type') !== 'refresh') {
                return response()->json(['error' => 'Invalid refresh token'], 401);
            }

            $user = User::find($payload->get('sub'));

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            
            $newAccessToken  = JWTAuth::fromUser($user);
            // $newRefreshToken = $this->createRefreshToken($user);

            return response()->json([
                'access'  => $newAccessToken,
                // 'refresh' => $newRefreshToken,
            ]);

        } catch (TokenExpiredException $e) { 
            return response()->json(['error' => 'Refresh token expired'], 401);
        } catch (TokenInvalidException $e) { 
            return response()->json(['error' => 'Invalid refresh token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token error'], 500);
        }
    }

    
    private function createRefreshToken($user = null)
    {
        $user = $user ?: auth()->user();

        $payload = [
            'sub'  => $user->id,
            'type' => 'refresh', 
            'iat'  => now()->timestamp,
            'exp'  => now()->addDays(30)->timestamp, 
        ];

        return JWTAuth::getJWTProvider()->encode($payload);
    }
}
