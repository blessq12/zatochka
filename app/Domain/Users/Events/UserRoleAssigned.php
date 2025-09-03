<?php

namespace App\Domain\Users\Events;

class UserRoleAssigned
{
    public function __construct(
        public readonly int $userId,
        public readonly string $role
    ) {
    }
}
