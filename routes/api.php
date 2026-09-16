<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    foreach (glob(__DIR__.'/api/*/api.php') ?: [] as $routeFile) {
        require $routeFile;
    }
});

foreach ([
    __DIR__.'/api/portal/auth.php',
    __DIR__.'/api/portal/client.php',
    __DIR__.'/api/portal/public_orders.php',
    __DIR__.'/api/site_content/public.php',
] as $portalRouteFile) {
    require $portalRouteFile;
}
