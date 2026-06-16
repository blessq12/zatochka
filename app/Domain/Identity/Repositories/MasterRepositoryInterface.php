<?php

namespace App\Domain\Identity\Repositories;

use App\Domain\Identity\Entities\Master;

interface MasterRepositoryInterface
{
    public function findById(int $id): ?Master;

    public function findByEmail(string $email): ?Master;

    public function save(Master $master): Master;
}
