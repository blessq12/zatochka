<?php

namespace App\Infrastructure\Crm\ReadModel;

use App\Application\Crm\Port\ActorEmailResolver;
use Illuminate\Support\Facades\DB;

final class EloquentActorEmailResolver implements ActorEmailResolver
{
    public function resolve(string $actorType, int $actorId): ?string
    {
        $email = DB::table('identity_actor_links')
            ->join('identities', 'identities.id', '=', 'identity_actor_links.identity_id')
            ->where('identity_actor_links.actor_type', $actorType)
            ->where('identity_actor_links.actor_id', $actorId)
            ->value('identities.email');

        return $email !== null ? (string) $email : null;
    }
}
