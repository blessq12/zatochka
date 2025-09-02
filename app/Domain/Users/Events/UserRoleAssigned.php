<?php

namespace App\Domain\Users\Events;

use App\Domain\Users\ValueObjects\UserId;

class UserRoleAssigned
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public readonly UserId $userId,
        public readonly array $roles
    ) {}
}
