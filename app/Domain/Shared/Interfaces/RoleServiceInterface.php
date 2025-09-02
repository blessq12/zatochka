<?php

namespace App\Domain\Shared\Interfaces;

use App\Domain\Users\ValueObjects\UserId;

interface RoleServiceInterface
{
    /**
     * @param string[] $roles
     */
    public function assignRoles(UserId $userId, array $roles): void;

    /**
     * @return string[]
     */
    public function getRoles(UserId $userId): array;
}
