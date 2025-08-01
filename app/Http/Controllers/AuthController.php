<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)

    {
        //  Validation block 
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
            
            
            return response()->json([
                'status' => 'success',
                'user' => [
                    'username' => $user->username,
                    'role' => $user->role,
                ],
                'token' => [
                    'access_token' => $token,
                    
                ]
                
                
            ]);
            echo("test user1");

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
}
