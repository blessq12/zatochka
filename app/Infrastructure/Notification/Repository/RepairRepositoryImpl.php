<?php

namespace App\Infrastructure\Notification\Repository;

use App\Domain\Notification\Entity\Repair;
use App\Domain\Notification\Repository\RepairRepository;
use App\Domain\Notification\Mapper\RepairMapper;
use App\Models\Repair as RepairModel;

class RepairRepositoryImpl implements RepairRepository
{
    public function __construct(
        private RepairMapper $mapper
    ) {}

    public function create(array $data): Repair
    {
        // TODO: Implement create logic
        return new Repair(id: null);
    }

    public function get(int $id): ?Repair
    {
        // TODO: Implement get logic
        return null;
    }

    public function update(Repair $repair, array $data): Repair
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
