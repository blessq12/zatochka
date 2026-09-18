<?php

namespace App\Infrastructure\Identity\Repository;

use App\Domain\Identity\Aggregate\Identity;
use App\Domain\Identity\Repository\IdentityRepository;
use App\Infrastructure\Identity\Eloquent\IdentityModel;

final class EloquentIdentityRepository implements IdentityRepository
{
    public function save(Identity $identity): Identity
    {
        $model = $identity->id() === null
            ? new IdentityModel()
            : IdentityModel::query()->findOrFail($identity->id());

        $model->email = $identity->email();
        $model->password = $identity->passwordHash();
        $model->save();

        if ($identity->id() === null) {
            $identity->assignId((int) $model->id);
        }

        return $identity;
    }

    public function findById(int $id): ?Identity
    {
        $model = IdentityModel::query()->find($id);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByEmail(string $email): ?Identity
    {
        $model = IdentityModel::query()->where('email', $email)->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function deleteById(int $id): void
    {
        $model = IdentityModel::query()->find($id);

        if ($model === null) {
            return;
        }

        $model->tokens()->delete();
        $model->delete();
    }

    private function toDomain(IdentityModel $model): Identity
    {
        return new Identity(
            (int) $model->id,
            (string) $model->email,
            (string) $model->password,
        );
    }
}
