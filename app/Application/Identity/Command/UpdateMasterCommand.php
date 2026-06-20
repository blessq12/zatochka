<?php

namespace App\Application\Identity\Command;

final readonly class UpdateMasterCommand
{
    public function __construct(
        public int $id,
        public string $name,
        public string $surname,
        public string $email,
        public ?string $phone,
        public ?string $password,
    ) {}
}
