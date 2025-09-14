<?php

namespace App\Infrastructure\Repair\Repository;

use App\Domain\Repair\Entity\Tool;
use App\Domain\Repair\Repository\ToolRepository;
use App\Domain\Repair\Mapper\ToolMapper;
use App\Models\Tool as ToolModel;

class ToolRepositoryImpl implements ToolRepository
{
    public function __construct(
        private ToolMapper $mapper
    ) {}

    public function create(array $data): Tool
    {
        // TODO: Implement create logic
        return new Tool(id: null);
    }

    public function get(int $id): ?Tool
    {
        // TODO: Implement get logic
        return null;
    }

    public function update(Tool $repair, array $data): Tool
    {
        // TODO: Implement update logic
        return $repair;
    }

    public function delete(int $id): bool
    {
        // TODO: Implement delete logic
        return false;
    }

    public function exists(int $id): bool
    {
        // TODO: Implement exists logic
        return false;
    }

    public function getAll(): array
    {
        // TODO: Implement get all logic
        return [];
    }
}
