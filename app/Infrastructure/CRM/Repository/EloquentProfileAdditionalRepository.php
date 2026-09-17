<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Aggregate\ProfileAdditional;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;
use App\Infrastructure\Crm\Eloquent\ProfileAdditionalModel;

final class EloquentProfileAdditionalRepository implements ProfileAdditionalRepository
{
    public function save(ProfileAdditional $profile): ProfileAdditional
    {
        $model = $profile->id() === null
            ? new ProfileAdditionalModel()
            : ProfileAdditionalModel::withTrashed()->findOrFail($profile->id());

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

        return new ProfileAdditional((int) $model->id, false);
    }

    public function delete(ProfileAdditional $profile): void
    {
        $profile->markDeleted();
        $this->save($profile);
    }
}
