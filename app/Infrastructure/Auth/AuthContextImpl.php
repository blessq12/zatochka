<?php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\AuthContextInterface;
use App\Domain\Company\Entity\User;
use App\Domain\Company\Mapper\UserMapper;
use Illuminate\Support\Facades\Auth;

class AuthContextImpl implements AuthContextInterface
{
    private ?User $currentUser = null;
    private bool $userLoaded = false;

    public function __construct(
        private UserMapper $userMapper
    ) {}

    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    public function getCurrentUserId(): ?int
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return Auth::id();
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        // Ленивая загрузка пользователя
        if (!$this->userLoaded) {
            $eloquentUser = Auth::user();
            if ($eloquentUser) {
                $this->currentUser = $this->userMapper->toDomain($eloquentUser);
            }
            $this->userLoaded = true;
        }

        return $this->currentUser;
    }

    public function hasRole(string $role): bool
    {
        $user = $this->getCurrentUser();
        return $user ? $user->hasRole($role) : false;
    }

    public function hasAnyRole(array $roles): bool
    {
        $user = $this->getCurrentUser();
        return $user ? $user->hasAnyRole($roles) : false;
    }
}
