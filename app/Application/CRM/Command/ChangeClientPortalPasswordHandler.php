<?php

namespace App\Application\CRM\Command;

use App\Models\User;
use App\Models\UserRole;
use App\Shared\Domain\DomainException;

final readonly class ChangeClientPortalPasswordHandler
{
    public function handle(ChangeClientPortalPasswordCommand $command): void
    {
        /** @var User|null $user */
        $user = User::query()->find($command->userId);

        if ($user === null || $user->role !== UserRole::Client) {
            throw new DomainException('Client portal user not found.');
        }

        $user->password = $command->password;
        $user->requires_password_set = false;
        $user->save();
    }
}
