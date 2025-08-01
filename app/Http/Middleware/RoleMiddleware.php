<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

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
        // ✅ Use JWTAuth to get the user from the token
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthorized. Please login.',
            ], 401);
        }

        // ✅ Check if user's role is allowed
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Forbidden. You do not have access to this resource.',
            ], 403);
        }

        return $next($request);
    }
}
