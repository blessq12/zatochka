<?php

namespace App\Application\Identity\Command;

use App\Application\Identity\Port\PasswordHasher;
use App\Domain\Identity\Entity\StaffUser;
use App\Domain\Identity\Repository\StaffUserRepository;
use App\Domain\Identity\VO\StaffRole;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class RegisterStaffUserHandler
{
    public function __construct(
        private StaffUserRepository $users,
        private PasswordHasher $passwords,
    ) {}

    public function handle(RegisterStaffUserCommand $command): void
    {
        $role = StaffRole::tryFrom($command->role)
            ?? throw new DomainException('Unknown staff role.');

        if ($this->users->emailExists($command->email)) {
            throw new DomainException('Staff email is already taken.');
        }

        $user = StaffUser::register(
            new EntityId($command->userId),
            $command->name,
            $command->email,
            $role,
            $this->passwords->hash($command->plainPassword),
        );

        $this->users->save($user);
    }
}
