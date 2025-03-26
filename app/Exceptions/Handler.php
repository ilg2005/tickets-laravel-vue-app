<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Handler extends ExceptionHandler
{
    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, $exception): Response|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Exception $exception): Response|JsonResponse
    {
        if ($exception instanceof AuthorizationException) {
            return response()->json(['error' => $exception->getMessage()], 403);
        }

        return parent::render($request, $exception);
    }
}
