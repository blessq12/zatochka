<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\Order\OrderCreated;
use App\Events\Order\OrderStatusChanged;
use App\Events\Bonus\BonusEarned;
use App\Events\Bonus\BonusSpent;
use App\Events\Bonus\BonusExpiring;
use App\Listeners\Order\HandleOrderCreated;
use App\Listeners\Order\HandleOrderStatusChanged;
use App\Listeners\Bonus\SendBonusEarnedNotification;
use App\Listeners\Bonus\SendBonusSpentNotification;
use App\Listeners\Bonus\SendBonusExpiringNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderCreated::class => [
            [HandleOrderCreated::class, 'handle'],
        ],
        OrderStatusChanged::class => [
            [HandleOrderStatusChanged::class, 'handle'],
        ],
        BonusEarned::class => [
            [SendBonusEarnedNotification::class, 'handle'],
        ],
        BonusSpent::class => [
            [SendBonusSpentNotification::class, 'handle'],
        ],
        BonusExpiring::class => [
            [SendBonusExpiringNotification::class, 'handle'],
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
