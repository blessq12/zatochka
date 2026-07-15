<?php

namespace App\Application\Identity\Command;

final readonly class AssignRoleCommand
{
    public function __construct(
        public int $employeeId,
        public int $roleId,
    ) {}
}
