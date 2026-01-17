<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        // В production логируем все ошибки, исключения и запросы с ошибками
        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            if ($isLocal) {
                return true; // В локальной среде логируем всё
            }

            // В production логируем:
            // - Все исключения
            // - Запросы с ошибками (4xx, 5xx)
            // - Неудачные задачи
            // - Запланированные задачи
            // - Запросы с тегами
            // - Медленные запросы к БД
            return $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag() ||
                ($entry->type === 'exception') ||
                ($entry->type === 'request' && ($entry->content['response_status'] ?? 0) >= 400) ||
                ($entry->type === 'query' && ($entry->content['slow'] ?? false));
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            // Разрешаем доступ root пользователю и админам
            return in_array($user->email, [
                'root@root.com',
            ]) || ($user->role && (in_array('manager', $user->role) || in_array('admin', $user->role)));
        });
    }
}
