<?php

namespace App\Application\Identity\Port;

interface MasterTokenIssuer
{
    public function issueToken(int $masterId, string $tokenName = 'pos'): string;
}
