<?php

namespace App\Infrastructure\Crm\Provider;

use App\Application\Crm\Listener\CreateActorOnRegistrationRequested;
use App\Domain\Crm\Repository\ClientRepository;
use App\Domain\Crm\Repository\ManagerRepository;
use App\Domain\Crm\Repository\MasterRepository;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;
use App\Infrastructure\Crm\Repository\EloquentClientRepository;
use App\Infrastructure\Crm\Repository\EloquentManagerRepository;
use App\Infrastructure\Crm\Repository\EloquentMasterRepository;
use App\Infrastructure\Crm\Repository\EloquentProfileAdditionalRepository;
use App\Providers\ContextServiceProvider;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\ActorRegistrationRequested;

final class CrmServiceProvider extends ContextServiceProvider
{
    protected function bindings(): array
    {
        return [
            ClientRepository::class => EloquentClientRepository::class,
            ManagerRepository::class => EloquentManagerRepository::class,
            MasterRepository::class => EloquentMasterRepository::class,
            ProfileAdditionalRepository::class => EloquentProfileAdditionalRepository::class,
        ];
    }

    public function boot(): void
    {
        $this->app->make(EventBus::class)->listen(
            ActorRegistrationRequested::class,
            CreateActorOnRegistrationRequested::class,
        );
    }
}
