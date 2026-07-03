<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\Branch;

interface BranchRepositoryInterface
{
    public function findById(int $id): ?Branch;

    public function findFirstActive(): ?Branch;

    public function save(Branch $branch): Branch;
}
