<?php

namespace App\Providers;

use App\Application\SiteContent\Query\GetSiteBootstrapHandler;
use App\Application\SiteContent\Query\GetSiteBootstrapQuery;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $content = $this->app->make(GetSiteBootstrapHandler::class)
            ->handle(new GetSiteBootstrapQuery());

        foreach ($content as $key => $value) {
            View::share($key, $value);
        }

        View::share(
            'current_path',
            '/'.ltrim((string) request()->path(), '/'),
        );
    }
}
