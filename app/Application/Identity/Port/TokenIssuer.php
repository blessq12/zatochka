<?php

namespace App\Application\Identity\Port;

interface TokenIssuer
{
    public function issue(int $identityId, string $tokenName = 'api'): string;
}
