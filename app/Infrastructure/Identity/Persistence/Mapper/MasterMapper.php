<?php

namespace App\Infrastructure\Identity\Persistence\Mapper;

use App\Domain\Identity\Entity\Master;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;

final class MasterMapper
{
    public function toDomain(UserModel $model): Master
    {
        return new Master(
            id: $model->id,
            name: $model->name,
            surname: $model->surname ?? '',
            email: $model->email,
            phone: $model->phone,
            notificationsEnabled: $model->notifications_enabled,
        );
    }

    public function fillModel(Master $master, UserModel $model): void
    {
        $model->fill([
            'name' => $master->name(),
            'surname' => $master->surname(),
            'email' => $master->email(),
            'phone' => $master->phone(),
            'notifications_enabled' => $master->notificationsEnabled(),
        ]);
    }
}
