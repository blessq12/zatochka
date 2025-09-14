<?php

namespace App\Domain\Auth;

interface AuthContextInterface
{
    public function isAuthenticated(): bool;

    public function getCurrentUserId(): ?int;

    public function getCurrentUser(): ?\App\Domain\Company\Entity\User;

    public function hasRole(string $role): bool;

    public function hasAnyRole(array $roles): bool;
}
