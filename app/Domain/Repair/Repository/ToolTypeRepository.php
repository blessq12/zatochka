<?php

namespace App\Domain\Repair\Repository;

use App\Domain\Repair\Entity\ToolType;

interface ToolTypeRepository
{
    public function create(array $data): ToolType;
    public function get(int $id): ?ToolType;
    public function update(ToolType $toolType, array $data): ToolType;
    public function delete(int $id): bool;
    public function exists(int $id): bool;
    public function getAll(): array;
}
