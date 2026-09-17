<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Aggregate\Identity;

interface IdentityRepository
{
    public function save(Identity $identity): Identity;

    public function findById(int $id): ?Identity;

    public function findByEmail(string $email): ?Identity;

    public function deleteById(int $id): void;
}
