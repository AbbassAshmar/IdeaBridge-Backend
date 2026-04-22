<?php

use App\Exceptions\ApplicationError;
use App\Utils\ResponseHelper;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $throwable, Request $request) {
            if ($throwable instanceof ValidationException) {
                return response()->json(ResponseHelper::failure([
                    'title' => 'Validation Error',
                    'message' => 'The given data was invalid.',
                    'details' => (object) $throwable->errors(),
                ]), 422);
            }

            if ($throwable instanceof AuthenticationException) {
                return response()->json(ResponseHelper::failure([
                    'title' => 'Authentication Error',
                    'message' => $throwable->getMessage() ?: 'Unauthenticated.',
                    'details' => (object) [],
                ]), 401);
            }

            if ($throwable instanceof AuthorizationException) {
                return response()->json(ResponseHelper::failure([
                    'title' => 'Authorization Error',
                    'message' => $throwable->getMessage() ?: 'This action is unauthorized.',
                    'details' => (object) [],
                ]), 403);
            }

            if ($throwable instanceof ModelNotFoundException) {
                return response()->json(ResponseHelper::failure([
                    'title' => 'Not Found Error',
                    'message' => 'The requested resource was not found.',
                    'details' => (object) [],
                ]), 404);
            }

            if ($throwable instanceof ApplicationError) {
                return response()->json(ResponseHelper::failure($throwable->toResponseError()), $throwable->status());
            }

            return response()->json(ResponseHelper::failure([
                'title' => 'Server Error',
                'message' => config('app.debug') ? $throwable->getMessage() : 'An unexpected error occurred.',
                'details' => (object) [
                    'exception' => class_basename($throwable),
                ],
            ]), 500);
        });
    })->create();
