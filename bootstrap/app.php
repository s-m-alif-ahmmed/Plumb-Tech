<?php

use App\Helpers\Helper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => App\Http\Middleware\AdminMiddleware::class,
            'role' =>  App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                if ($e instanceof ValidationException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 422,$e->errors());
                }

                if ($e instanceof ModelNotFoundException) {
                    return Helper::jsonErrorResponse($e->getMessage(), 404);
                }

                if ($e instanceof AuthenticationException) {
                    return Helper::jsonErrorResponse( $e->getMessage(), 401);
                }
                if ($e instanceof AuthorizationException) {
                    return Helper::jsonErrorResponse( $e->getMessage(), 403);
                }
                // Dynamically determine the status code if available
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return Helper::jsonErrorResponse($e->getMessage(), $statusCode);
            }else{
                return null;
            }
        });
    })->create();
