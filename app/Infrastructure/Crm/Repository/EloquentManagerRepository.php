<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Aggregate\Manager;
use App\Domain\Crm\Repository\ManagerRepository;
use App\Infrastructure\Crm\Eloquent\ManagerModel;

final class EloquentManagerRepository extends EloquentActorRepository implements ManagerRepository
{
    public function __construct()
    {
        parent::__construct(
            ManagerModel::class,
            static fn (ManagerModel $model): Manager => new Manager(
                (int) $model->id,
                (int) $model->profile_additional_id,
                false,
            ),
        );
    }
}
