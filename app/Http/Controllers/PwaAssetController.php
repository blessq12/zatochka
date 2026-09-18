<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

final class PwaAssetController extends Controller
{
    private const APPS = ['client', 'manager', 'master'];

    public function manifest(string $app): Response
    {
        $this->assertApp($app);
        $path = resource_path("pwa/{$app}/manifest.webmanifest");
        abort_unless(is_file($path), 404);

        return response((string) file_get_contents($path), 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }

    public function serviceWorker(string $app): Response
    {
        $this->assertApp($app);
        $path = resource_path("pwa/{$app}/sw.js");
        abort_unless(is_file($path), 404);

        return response((string) file_get_contents($path), 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => "/{$app}/",
            'Cache-Control' => 'no-cache',
        ]);
    }

    private function assertApp(string $app): void
    {
        abort_unless(in_array($app, self::APPS, true), 404);
    }
}
