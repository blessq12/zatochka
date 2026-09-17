<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Aggregate\ProfileAdditional;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;
use App\Infrastructure\Crm\Eloquent\ProfileAdditionalModel;
use DateTimeImmutable;

final class EloquentProfileAdditionalRepository implements ProfileAdditionalRepository
{
    public function save(ProfileAdditional $profile): ProfileAdditional
    {
        $model = $profile->id() === null
            ? new ProfileAdditionalModel()
            : ProfileAdditionalModel::withTrashed()->findOrFail($profile->id());

        $model->name = $profile->name();
        $model->phone = $profile->phone();
        $model->birthday = $profile->birthday()?->format('Y-m-d');
        $model->delivery_address = $profile->deliveryAddress();
        $model->save();

        if ($profile->id() === null) {
            $profile->assignId((int) $model->id);
        }

        if ($profile->isDeleted() && $model->deleted_at === null) {
            $model->delete();
        }

        return $profile;
    }

    public function findById(int $id): ?ProfileAdditional
    {
        $model = ProfileAdditionalModel::query()->find($id);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function delete(ProfileAdditional $profile): void
    {
        $profile->markDeleted();
        $this->save($profile);
    }

    private function toDomain(ProfileAdditionalModel $model): ProfileAdditional
    {
        $birthday = $model->birthday !== null
            ? new DateTimeImmutable((string) $model->birthday->format('Y-m-d'))
            : null;

        return new ProfileAdditional(
            (int) $model->id,
            $model->name !== null ? (string) $model->name : null,
            $model->phone !== null ? (string) $model->phone : null,
            $birthday,
            $model->delivery_address !== null ? (string) $model->delivery_address : null,
            false,
        );
    }
}
