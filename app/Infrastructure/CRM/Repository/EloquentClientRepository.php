<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Aggregate\Client;
use App\Domain\Crm\Repository\ClientRepository;
use App\Infrastructure\Crm\Eloquent\ClientModel;

final class EloquentClientRepository extends EloquentActorRepository implements ClientRepository
{
    public function __construct()
    {
        parent::__construct(
            ClientModel::class,
            static fn (ClientModel $model): Client => new Client(
                (int) $model->id,
                (int) $model->profile_additional_id,
                false,
            ),
        );
    }
}
