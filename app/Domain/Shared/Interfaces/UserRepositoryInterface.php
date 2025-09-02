<?php

namespace App\Domain\Shared\Interfaces;

use App\Domain\Users\Entities\User;
use App\Domain\Users\ValueObjects\Email;
use App\Domain\Users\ValueObjects\UserId;

interface UserRepositoryInterface
{
    public function nextId(): UserId;

    public function save(User $user): void;

    public function getById(UserId $id): ?User;

    public function getByEmail(Email $email): ?User;

    public function existsByEmail(Email $email): bool;
}
