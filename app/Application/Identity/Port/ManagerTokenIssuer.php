<?php

namespace App\Application\Identity\Port;

interface ManagerTokenIssuer
{
    public function issueToken(int $managerId, string $tokenName = 'manager'): string;
}
