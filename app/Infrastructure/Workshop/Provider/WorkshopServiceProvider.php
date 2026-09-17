<?php

namespace App\Infrastructure\Workshop\Provider;

use App\Application\Workshop\Listener\ReopenWorkshopJobOnOrderReturnedToRework;
use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Infrastructure\Workshop\Repository\EloquentWorkshopJobRepository;
use App\Providers\ContextServiceProvider;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\OrderReturnedToRework;

final class WorkshopServiceProvider extends ContextServiceProvider
{
    protected function bindings(): array
    {
        return [
            WorkshopJobRepository::class => EloquentWorkshopJobRepository::class,
        ];
    }

    public function boot(): void
    {
        $bus = $this->app->make(EventBus::class);
        $bus->listen(OrderReturnedToRework::class, ReopenWorkshopJobOnOrderReturnedToRework::class);
    }
}
