<?php

namespace App\Infrastructure\Notification\Repository;

use App\Domain\Notification\Entity\EquipmentType;
use App\Domain\Notification\Repository\EquipmentTypeRepository;
use App\Domain\Notification\Mapper\EquipmentTypeMapper;
use App\Models\EquipmentType as EquipmentTypeModel;

class EquipmentTypeRepositoryImpl implements EquipmentTypeRepository
{
    public function __construct(
        private EquipmentTypeMapper $mapper
    ) {}

    public function create(array $data): EquipmentType
    {
        // TODO: Implement create logic
        return new EquipmentType(id: null);
    }

    public function get(int $id): ?EquipmentType
    {
        // TODO: Implement get logic
        return null;
    }

    public function update(EquipmentType $equipmentType, array $data): EquipmentType
    {
        // TODO: Implement update logic
        return $equipmentType;
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
