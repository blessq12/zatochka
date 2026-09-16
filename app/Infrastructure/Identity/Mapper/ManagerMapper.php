<?php

namespace App\Infrastructure\Identity\Mapper;

use App\Domain\Identity\Entity\Manager;
use App\Infrastructure\Identity\Model\ManagerModel;
use App\Shared\ValueObject\EntityId;

final class ManagerMapper
{
    public function toDomain(ManagerModel $model): Manager
    {
        return Manager::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->name,
            (string) $model->email,
            (string) $model->password,
        );
    }

    public function toPersistence(Manager $manager, ?ManagerModel $model = null): ManagerModel
    {
        $model ??= new ManagerModel;
        $model->id = $manager->id()->value;
        $model->name = $manager->name();
        $model->email = $manager->email();
        $model->password = $manager->passwordHash();

        return $model;
    }
}
