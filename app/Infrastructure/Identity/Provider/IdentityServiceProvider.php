<?php

namespace App\Infrastructure\Identity\Provider;

use App\Application\Identity\Listener\LinkIdentityOnActorCreated;
use App\Application\Identity\Listener\UnlinkAndDeleteIdentityOnActorSoftDeleted;
use App\Application\Identity\Port\TokenIssuer;
use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Domain\Identity\Repository\IdentityRepository;
use App\Infrastructure\Identity\Auth\SanctumTokenIssuer;
use App\Infrastructure\Identity\Repository\EloquentIdentityActorLinkRepository;
use App\Infrastructure\Identity\Repository\EloquentIdentityRepository;
use App\Providers\ContextServiceProvider;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\ActorCreated;
use App\Shared\IntegrationEvents\ActorSoftDeleted;

final class IdentityServiceProvider extends ContextServiceProvider
{
    protected function bindings(): array
    {
        return [
            IdentityRepository::class => EloquentIdentityRepository::class,
            IdentityActorLinkRepository::class => EloquentIdentityActorLinkRepository::class,
            TokenIssuer::class => SanctumTokenIssuer::class,
        ];
    }

    public function boot(): void
    {
        $bus = $this->app->make(EventBus::class);

        $bus->listen(ActorCreated::class, LinkIdentityOnActorCreated::class);
        $bus->listen(ActorSoftDeleted::class, UnlinkAndDeleteIdentityOnActorSoftDeleted::class);
    }
}
