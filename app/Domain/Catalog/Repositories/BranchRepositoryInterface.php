<?php

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Entities\Branch;

interface BranchRepositoryInterface
{
    public function findById(int $id): ?Branch;

    public function save(Branch $branch): Branch;
}
