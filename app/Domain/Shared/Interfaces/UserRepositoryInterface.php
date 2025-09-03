<?php

namespace App\Domain\Shared\Interfaces;

use App\Domain\Users\Entities\User;
use App\Domain\Users\ValueObjects\Email;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function getById(int $id): ?User;

    public function getByEmail(Email $email): ?User;

    public function existsByEmail(Email $email): bool;
}
