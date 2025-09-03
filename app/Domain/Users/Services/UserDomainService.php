<?php

namespace App\Domain\Users\Services;

use App\Domain\Shared\Interfaces\PasswordHasherInterface;
use App\Domain\Shared\Interfaces\RoleServiceInterface;
use App\Domain\Shared\Interfaces\UserRepositoryInterface;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Events\UserRegistered;
use App\Domain\Users\ValueObjects\Email;
use App\Domain\Users\ValueObjects\UserId;
use App\Domain\Shared\Events\EventBusInterface;

class UserDomainService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasherInterface $hasher,
        private readonly RoleServiceInterface $roles,
        private readonly EventBusInterface $events
    ) {
    }

    public function register(string $name, string $email, string $plainPassword, array $roles = []): User
    {
        $emailVO = Email::fromString($email);
        if ($this->users->existsByEmail($emailVO)) {
            throw new \InvalidArgumentException('User with email already exists');
        }

        $userId = $this->users->nextId();
        $passwordHash = $this->hasher->hash($plainPassword);

        $user = User::register($userId, $name, $emailVO, $passwordHash);

        if (!empty($roles)) {
            $user->assignRoles($roles);
        }

        $this->users->save($user);
        if (!empty($roles)) {
            $this->roles->assignRoles($user->userId(), $user->roles());
        }

        $this->events->publish(new UserRegistered($user->userId(), $user->name(), $user->email()));

        return $user;
    }
}
