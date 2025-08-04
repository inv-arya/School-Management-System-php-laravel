<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

    
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'status' => 'fail',
            'message' => 'Unauthorized. Token is missing or invalid.',
        ], 401);
    }
}
