<?php

namespace App\Application\Identity\Command;

final readonly class GrantPermissionCommand
{
    public function __construct(
        public int $roleId,
        public int $permissionId,
        public string $permissionCode,
        public string $description,
    ) {}
}
