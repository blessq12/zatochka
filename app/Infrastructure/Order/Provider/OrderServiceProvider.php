<?php

namespace App\Infrastructure\Order\Provider;

use App\Application\Order\Listener\MarkOrderInProgressOnAcceptedIntoWork;
use App\Application\Order\Listener\MarkOrderWorksCompletedOnWorkshopComplete;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\Repository\OrderReviewRepository;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use App\Infrastructure\Order\Repository\EloquentOrderReviewRepository;
use App\Providers\ContextServiceProvider;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\OrderAcceptedIntoWork;
use App\Shared\IntegrationEvents\WorkshopWorksCompleted;

final class OrderServiceProvider extends ContextServiceProvider
{
    protected function bindings(): array
    {
        return [
            OrderRepository::class => EloquentOrderRepository::class,
            OrderReviewRepository::class => EloquentOrderReviewRepository::class,
        ];
    }

    public function boot(): void
    {
        $bus = $this->app->make(EventBus::class);
        $bus->listen(OrderAcceptedIntoWork::class, MarkOrderInProgressOnAcceptedIntoWork::class);
        $bus->listen(WorkshopWorksCompleted::class, MarkOrderWorksCompletedOnWorkshopComplete::class);
    }
}
