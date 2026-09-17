<?php

return [
    App\Shared\EventBus\EventBusServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Infrastructure\SiteContent\Provider\SiteContentServiceProvider::class,
    App\Infrastructure\Crm\Provider\CrmServiceProvider::class,
    App\Infrastructure\Identity\Provider\IdentityServiceProvider::class,
    App\Infrastructure\Order\Provider\OrderServiceProvider::class,
    App\Infrastructure\Workshop\Provider\WorkshopServiceProvider::class,
    App\Infrastructure\Finance\Provider\FinanceServiceProvider::class,
    App\Infrastructure\Warehouse\Provider\WarehouseServiceProvider::class,
];
