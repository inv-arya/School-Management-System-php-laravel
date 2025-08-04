<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Exception;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {   
        try{

            if (!$request->header('Authorization')) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Unauthorized. Token not provided.',
                ], 401);
            }
        
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Unauthorized. Please login.',
                ], 401);
            }

            
            if (!in_array($user->role, $roles)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Forbidden. You do not have access to this resource.',
                ], 403);
            }

            return $next($request);
        }
        catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthorized. Invalid or expired token.',
            ], 401);
        }

    }
}
