<?php

namespace App\Domain\Shared\Interfaces;

interface RoleServiceInterface
{
    /**
     * @param string[] $roles
     */
    public function assignRoles(int $userId, array $roles): void;

    /**
     * @return string[]
     */
    public function getRoles(int $userId): array;
}
