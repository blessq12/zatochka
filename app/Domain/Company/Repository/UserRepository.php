<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\User;

interface UserRepository
{
    public function create(array $data): User;
    public function get(int $id): ?User;
    public function update(User $user, array $data): User;
    public function delete(int $id): bool;
    public function exists(int $id): bool;
    public function getAll(): array;
}
