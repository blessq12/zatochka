<?php

namespace App\Infrastructure\CRM\Mapper;

use App\Domain\CRM\Entity\Manager;
use App\Infrastructure\CRM\Model\ManagerModel;
use App\Shared\ValueObject\EntityId;

final class ManagerMapper
{
    public function toDomain(ManagerModel $model): Manager
    {
        return Manager::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->name,
            (string) $model->email,
        );
    }

    public function toPersistence(Manager $manager, ?ManagerModel $model = null): ManagerModel
    {
        $model ??= new ManagerModel;
        $model->id = $manager->id()->value;
        $model->name = $manager->name();
        $model->email = $manager->email();

        return $model;
    }
}
