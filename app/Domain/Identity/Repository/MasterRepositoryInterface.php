<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\Master;

interface MasterRepositoryInterface
{
    public function findById(int $id): ?Master;

    public function findByEmail(string $email): ?Master;

    public function save(Master $master): Master;
}
