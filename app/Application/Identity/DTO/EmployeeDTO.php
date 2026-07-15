<?php

namespace App\Application\Identity\DTO;

final readonly class EmployeeDTO
{
    /** @param list<string> $roleNames */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $active,
        public array $roleNames,
    ) {}
}
