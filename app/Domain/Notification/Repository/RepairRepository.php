<?php

namespace App\Domain\Notification\Repository;

use App\Domain\Notification\Entity\Notification;

interface NotificationRepository
{
    public function create(array $data): Notification;
    public function get(int $id): ?Notification;
    public function update(Notification $repair, array $data): Notification;
    public function delete(int $id): bool;
    public function exists(int $id): bool;
    public function getAll(): array;
}
