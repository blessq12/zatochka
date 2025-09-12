<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\MiddlewareServiceProvider::class,
    // filament panels providers
    App\Providers\Filament\ManagerPanelProvider::class,
    App\Providers\Filament\MasterPanelProvider::class,

    // fortify service provider
    App\Providers\FortifyServiceProvider::class,
    App\Providers\MiddlewareServiceProvider::class,
    // telescope service provider
    App\Providers\TelescopeServiceProvider::class,

    Laravel\Fortify\FortifyServiceProvider::class,

];
