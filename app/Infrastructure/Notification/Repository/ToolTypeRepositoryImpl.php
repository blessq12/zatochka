<?php

namespace App\Infrastructure\Notification\Repository;

use App\Domain\Notification\Entity\ToolType;
use App\Domain\Notification\Repository\ToolTypeRepository;
use App\Domain\Notification\Mapper\ToolTypeMapper;
use App\Models\ToolType as ToolTypeModel;

class ToolTypeRepositoryImpl implements ToolTypeRepository
{
    public function __construct(
        private ToolTypeMapper $mapper
    ) {}

    public function create(array $data): ToolType
    {
        // TODO: Implement create logic
        return new ToolType(id: null);
    }

    public function get(int $id): ?ToolType
    {
        // TODO: Implement get logic
        return null;
    }

    public function update(ToolType $toolType, array $data): ToolType
    {
        // TODO: Implement update logic
        return $toolType;
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
