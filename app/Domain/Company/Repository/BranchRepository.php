<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\Branch;

interface BranchRepository
{
    public function create(array $data): Branch;
    public function get(int $id): ?Branch;
    public function update(Branch $branch, array $data): Branch;
    public function delete(int $id): bool;
    public function exists(int $id): bool;
    public function getAll(): array;
    public function getMain(): ?Branch;
}
