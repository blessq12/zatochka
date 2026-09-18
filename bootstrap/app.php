<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\ConvertDomainException::class,
        ]);
        $middleware->alias([
            'actor' => \App\Http\Middleware\EnsureActorType::class,
        ]);

        // API: JSON 401, без route('login') → RouteNotFoundException
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\App\Shared\Domain\DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        });
        $exceptions->render(function (\App\Shared\Domain\ForbiddenException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        });
    })->create();
