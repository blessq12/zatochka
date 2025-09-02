<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\MiddlewareServiceProvider::class,
    App\Providers\Filament\ManagerPanelProvider::class,
    App\Providers\Filament\MasterPanelProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\MiddlewareServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    Laravel\Fortify\FortifyServiceProvider::class,
    App\Providers\Domain\BonusesDomainServiceProvider::class,
    App\Providers\Domain\OrdersDomainServiceProvider::class,
    App\Providers\Domain\ClientsDomainServiceProvider::class,
    App\Providers\Domain\InventoryDomainServiceProvider::class,
    App\Providers\Domain\UsersDomainServiceProvider::class,
];
