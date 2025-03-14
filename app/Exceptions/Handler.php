<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Ensure API requests get JSON responses
        if ($request->expectsJson()) {
            if ($exception instanceof AuthenticationException) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            if ($exception instanceof AuthorizationException) {
                return response()->json(['error' => 'You are not allowed'], 401);
            }

            if ($exception instanceof TokenInvalidException) {
                return response()->json(['error' => 'Token is invalid'], 401);
            }

            if ($exception instanceof TokenExpiredException) {
                return response()->json(['error' => 'Token has expired'], 401);
            }

            if ($exception instanceof JWTException) {
                return response()->json(['error' => 'Token is missing or incorrect'], 401);
            }

            if ($exception instanceof NotFoundHttpException) {
                return response()->json(['error' => 'API route not found'], 404);
            }
        }

        return parent::render($request, $exception);
    }
}
