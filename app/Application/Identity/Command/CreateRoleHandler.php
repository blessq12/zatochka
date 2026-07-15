<?php

namespace App\Application\Identity\Command;

use App\Domain\Identity\Entity\Role;
use App\Domain\Identity\Repository\RoleRepository;
use App\Shared\ValueObject\EntityId;

final readonly class CreateRoleHandler
{
    public function __construct(
        private RoleRepository $roles,
    ) {}

    public function handle(CreateRoleCommand $command): void
    {
        $this->roles->save(new Role(
            new EntityId($command->roleId),
            $command->name,
        ));
    }
}
