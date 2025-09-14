<?php

namespace App\Infrastructure\Notification\Repository;

use App\Domain\Notification\Entity\Tool;
use App\Domain\Notification\Repository\ToolRepository;
use App\Domain\Notification\Mapper\ToolMapper;
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

    public function update(Tool $tool, array $data): Tool
    {
        // TODO: Implement update logic
        return $tool;
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
