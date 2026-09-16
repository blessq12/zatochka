<?php

namespace App\Infrastructure\Identity\Mapper;

use App\Domain\Identity\Entity\Master;
use App\Infrastructure\Identity\Model\MasterModel;
use App\Shared\ValueObject\EntityId;

final class MasterMapper
{
    public function toDomain(MasterModel $model): Master
    {
        return Master::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->name,
            (string) $model->email,
            (string) $model->password,
        );
    }

    public function toPersistence(Master $master, ?MasterModel $model = null): MasterModel
    {
        $model ??= new MasterModel;
        $model->id = $master->id()->value;
        $model->name = $master->name();
        $model->email = $master->email();
        $model->password = $master->passwordHash();

        return $model;
    }
}
