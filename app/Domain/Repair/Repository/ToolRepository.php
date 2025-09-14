<?php

namespace App\Domain\Repair\Repository;

use App\Domain\Repair\Entity\Tool;

interface ToolRepository
{
    public function create(array $data): Tool;
    public function get(int $id): ?Tool;
    public function update(Tool $tool, array $data): Tool;
    public function delete(int $id): bool;
    public function exists(int $id): bool;
    public function getAll(): array;
}
