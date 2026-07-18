<?php

namespace App\Application\CRM\Command;

final readonly class RegisterClientPortalCommand
{
    public function __construct(
        public string $fullName,
        public string $email,
        public string $phone,
        public string $password,
    ) {}
}
