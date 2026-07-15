<?php

namespace App\Application\Identity\Command;

final readonly class HireEmployeeCommand
{
    public function __construct(
        public int $employeeId,
        public string $name,
        public string $email,
    ) {}
}
