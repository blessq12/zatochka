<?php

namespace App\Infrastructure\CRM\Mapper;

use App\Domain\CRM\Entity\Master;
use App\Infrastructure\CRM\Model\MasterModel;
use App\Shared\ValueObject\EntityId;

final class MasterMapper
{
    public function toDomain(MasterModel $model): Master
    {
        return Master::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->name,
            (string) $model->email,
        );
    }

    public function toPersistence(Master $master, ?MasterModel $model = null): MasterModel
    {
        $model ??= new MasterModel;
        $model->id = $master->id()->value;
        $model->name = $master->name();
        $model->email = $master->email();

        return $model;
    }
}
