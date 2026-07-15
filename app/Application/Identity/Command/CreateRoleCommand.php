<?php

namespace App\Application\Identity\Command;

final readonly class CreateRoleCommand
{
    public function __construct(
        public int $roleId,
        public string $name,
    ) {}
}
