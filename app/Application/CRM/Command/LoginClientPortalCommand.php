<?php

namespace App\Application\CRM\Command;

final readonly class LoginClientPortalCommand
{
    public function __construct(
        public string $phone,
        public string $password,
    ) {}
}
