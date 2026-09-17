<?php

return [
    App\Shared\EventBus\EventBusServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Infrastructure\SiteContent\Provider\SiteContentServiceProvider::class,
    App\Infrastructure\Crm\Provider\CrmServiceProvider::class,
    App\Infrastructure\Identity\Provider\IdentityServiceProvider::class,
];
