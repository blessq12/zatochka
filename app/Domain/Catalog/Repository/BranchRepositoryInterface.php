<?php

namespace App\Domain\Catalog\Repository;

use App\Domain\Catalog\Entity\Branch;

interface BranchRepositoryInterface
{
    public function findById(int $id): ?Branch;

    public function save(Branch $branch): Branch;
}
