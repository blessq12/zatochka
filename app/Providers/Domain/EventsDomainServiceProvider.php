<?php

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use App\Domain\Shared\Events\EventBusInterface;
use App\Infrastructure\Events\EventBus;
use App\Infrastructure\Events\Subscribers\UserRegisteredSubscriber;
use App\Domain\Users\Events\UserRegistered;

class EventsDomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Регистрируем EventBus как синглтон
        $this->app->singleton(EventBusInterface::class, EventBus::class);

        // Регистрируем слушатели доменных событий
        $this->registerEventListeners();
    }

    public function boot(): void
    {
        // Регистрируем слушатели в нашей кастомной системе событий
        $this->registerDomainEventListeners();
    }

    private function registerEventListeners(): void
    {
        // Регистрируем слушатели для доменных событий
        $this->app->bind(UserRegisteredSubscriber::class);
    }

    private function registerDomainEventListeners(): void
    {
        $eventBus = $this->app->make(EventBusInterface::class);
        $userRegisteredSubscriber = $this->app->make(UserRegisteredSubscriber::class);

        // Подписываем слушатели на доменные события
        $eventBus->subscribe(
            UserRegistered::class,
            [$userRegisteredSubscriber, 'handle']
        );
    }
}
