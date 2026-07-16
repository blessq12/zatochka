<?php

namespace App\Infrastructure\Shared\Persistence;

use App\Application\Shared\UnitOfWork;
use Illuminate\Support\Facades\DB;

final class EloquentUnitOfWork implements UnitOfWork
{
    public function execute(callable $operation): mixed
    {
        return DB::transaction($operation);
    }
}
