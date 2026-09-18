<?php

namespace App\Providers;

use App\Application\SiteContent\Query\GetSiteBootstrapHandler;
use App\Application\SiteContent\Query\GetSiteBootstrapQuery;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view): void {
            $content = $this->presentedSiteContent();

            foreach ($content as $key => $value) {
                $view->with($key, $value);
            }

            $view->with(
                'current_path',
                '/'.ltrim((string) request()->path(), '/'),
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function presentedSiteContent(): array
    {
        $request = request();

        if ($request->attributes->has('site_content_presented')) {
            /** @var array<string, mixed> $cached */
            $cached = $request->attributes->get('site_content_presented');

            return $cached;
        }

        $content = $this->siteBootstrap();
        $request->attributes->set('site_content_presented', $content);

        foreach ($content as $key => $value) {
            View::share($key, $value);
        }

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    private function siteBootstrap(): array
    {
        try {
            if (! Schema::hasTable('site_companies')) {
                return $this->emptyPresented();
            }

            return $this->app->make(GetSiteBootstrapHandler::class)
                ->handle(new GetSiteBootstrapQuery());
        } catch (Throwable) {
            return $this->emptyPresented();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPresented(): array
    {
        return [
            'company' => [],
            'contacts' => [
                'phone' => '',
                'phone_tel' => '',
                'messenger_write_url' => '',
                'social' => ['links' => []],
            ],
            'schedule' => ['days' => []],
            'faq' => ['items' => []],
            'delivery_info' => [
                'free_conditions' => [],
                'advantages' => [],
            ],
            'prices' => [],
            'sharpening_prices' => [],
            'repair_prices' => [],
        ];
    }
}
