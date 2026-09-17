<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Aggregate\Actor;
use App\Domain\Crm\Repository\ActorRepository;
use App\Infrastructure\Crm\Eloquent\ActorModel;
use Closure;

abstract class EloquentActorRepository implements ActorRepository
{
    /**
     * @param  class-string<ActorModel>  $modelClass
     * @param  Closure(ActorModel): Actor  $toDomain
     */
    public function __construct(
        private readonly string $modelClass,
        private readonly Closure $toDomain,
    ) {}

    public function save(Actor $actor): Actor
    {
        /** @var ActorModel $model */
        $model = $actor->id() === null
            ? new $this->modelClass()
            : $this->modelClass::withTrashed()->findOrFail($actor->id());

        $model->profile_additional_id = $actor->profileAdditionalId();
        $model->save();

        if ($actor->id() === null) {
            $actor->assignId((int) $model->id);
        }

        if ($actor->isDeleted() && $model->deleted_at === null) {
            $model->delete();
        }

        return $actor;
    }

    public function findById(int $id): ?Actor
    {
        /** @var ActorModel|null $model */
        $model = $this->modelClass::query()->find($id);

        if ($model === null) {
            return null;
        }

        return ($this->toDomain)($model);
    }

    public function delete(Actor $actor): void
    {
        $actor->markDeleted();
        $this->save($actor);
    }
}
