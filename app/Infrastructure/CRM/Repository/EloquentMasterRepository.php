<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Aggregate\Master;
use App\Domain\Crm\Repository\MasterRepository;
use App\Infrastructure\Crm\Eloquent\MasterModel;

final class EloquentMasterRepository extends EloquentActorRepository implements MasterRepository
{
    public function __construct()
    {
        parent::__construct(
            MasterModel::class,
            static fn (MasterModel $model): Master => new Master(
                (int) $model->id,
                (int) $model->profile_additional_id,
                false,
            ),
        );
    }
}
