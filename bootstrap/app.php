<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\App\Domain\OrderFulfillment\Exception\OrderPolicyViolation $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (\App\Domain\ClientPortal\Exception\SiteLeadPolicyViolation $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (\App\Domain\OrderFulfillment\Exception\OrderNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
        });
        $exceptions->render(function (\App\Domain\ClientPortal\Exception\ClientAlreadyRegisteredException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 409);
            }
        });

        $exceptions->render(function (\App\Domain\ClientPortal\Exception\ReviewPolicyViolation $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (\App\Domain\ClientPortal\Exception\ClientNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
        });

        $exceptions->render(function (\App\Domain\Warehouse\Exception\WarehouseItemNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
        });

        $exceptions->render(function (\App\Domain\Warehouse\Exception\WarehousePolicyViolation $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (\App\Domain\Equipment\Exception\EquipmentNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
        });
    })->create();
