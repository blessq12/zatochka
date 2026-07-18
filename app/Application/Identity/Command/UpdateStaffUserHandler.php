<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\StaffUserRepository;
use App\Domain\Identity\VO\StaffRole;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class UpdateStaffUserHandler
{
    public function __construct(
        private StaffUserRepository $users,
        private PasswordHasher $passwords,
    ) {}

    public function handle(UpdateStaffUserCommand $command): void
    {
        $role = StaffRole::tryFrom($command->role)
            ?? throw new DomainException('Unknown staff role.');

        $userId = new EntityId($command->userId);
        $user = $this->users->getById($userId);

        if ($this->users->emailExists($command->email, $userId)) {
            throw new DomainException('Staff email is already taken.');
        }

        $user->updateProfile($command->name, $command->email, $role);

        if (filled($command->plainPassword)) {
            $user->changePassword($this->passwords->hash((string) $command->plainPassword));
        }

        $this->users->save($user);
    }
}
