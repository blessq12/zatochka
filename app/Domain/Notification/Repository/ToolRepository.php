<?php

namespace App\Domain\Notification\Repository;

use App\Domain\Notification\Entity\Tool;

interface ToolRepository
{
    public function create(array $data): Tool;
    public function get(int $id): ?Tool;
    public function update(Tool $tool, array $data): Tool;
    public function delete(int $id): bool;
    public function exists(int $id): bool;
    public function getAll(): array;
}
