<?php

namespace App\Application\CRM\Command;

final readonly class UpdateMasterCommand
{
    public function __construct(
        public int $masterId,
        public string $name,
        public string $email,
    ) {}
}
