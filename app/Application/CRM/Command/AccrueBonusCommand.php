<?php

namespace App\Application\CRM\Command;

final readonly class AccrueBonusCommand
{
    public function __construct(
        public int $clientId,
        public string $amount,
    ) {}
}
