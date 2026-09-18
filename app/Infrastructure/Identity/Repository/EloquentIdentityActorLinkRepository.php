<?php

namespace App\Infrastructure\Identity\Repository;

use App\Domain\Identity\Link\IdentityActorLink;
use App\Domain\Identity\Link\IdentityActorLinkRepository;
use App\Infrastructure\Identity\Eloquent\IdentityActorLinkModel;

final class EloquentIdentityActorLinkRepository implements IdentityActorLinkRepository
{
    public function save(IdentityActorLink $link): void
    {
        IdentityActorLinkModel::query()->create([
            'identity_id' => $link->identityId,
            'actor_type' => $link->actorType,
            'actor_id' => $link->actorId,
        ]);
    }

    public function findByIdentityId(int $identityId): ?IdentityActorLink
    {
        $model = IdentityActorLinkModel::query()->where('identity_id', $identityId)->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByActor(string $actorType, int $actorId): ?IdentityActorLink
    {
        $model = IdentityActorLinkModel::query()
            ->where('actor_type', $actorType)
            ->where('actor_id', $actorId)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function deleteByActor(string $actorType, int $actorId): ?int
    {
        $model = IdentityActorLinkModel::query()
            ->where('actor_type', $actorType)
            ->where('actor_id', $actorId)
            ->first();

        if ($model === null) {
            return null;
        }

        $identityId = (int) $model->identity_id;
        $model->delete();

        return $identityId;
    }

    private function toDomain(IdentityActorLinkModel $model): IdentityActorLink
    {
        return new IdentityActorLink(
            (int) $model->identity_id,
            (string) $model->actor_type,
            (int) $model->actor_id,
        );
    }
}
