<?php

namespace App\Infrastructure\Identity\Persistence\Repository;

use App\Domain\Identity\Entity\Master;
use App\Domain\Identity\Repository\MasterRepositoryInterface;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\Identity\Persistence\Mapper\MasterMapper;

final class EloquentMasterRepository implements MasterRepositoryInterface
{
    public function __construct(
        private MasterMapper $mapper,
    ) {}

    public function findById(int $id): ?Master
    {
        $model = UserModel::query()->find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByEmail(string $email): ?Master
    {
        $model = UserModel::query()->where('email', $email)->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(Master $master): Master
    {
        $model = $master->id() !== null
            ? UserModel::query()->findOrFail($master->id())
            : new UserModel;

        $this->mapper->fillModel($master, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }
}
