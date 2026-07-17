<?php

namespace App\Application\Identity\Command;

use App\Application\Identity\Port\PasswordHasher;
use App\Domain\Identity\Repository\StaffUserRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ChangeStaffPasswordHandler
{
    public function __construct(
        private StaffUserRepository $users,
        private PasswordHasher $passwords,
    ) {}

    public function handle(ChangeStaffPasswordCommand $command): void
    {
        $user = $this->users->getById(new EntityId($command->userId));
        $user->changePassword($this->passwords->hash($command->plainPassword));
        $this->users->save($user);
    }
}
