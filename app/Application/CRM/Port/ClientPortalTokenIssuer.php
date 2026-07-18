<?php

namespace App\Application\CRM\Port;

interface ClientPortalTokenIssuer
{
    public function issueToken(int $clientId, string $tokenName = 'client-portal'): string;
}
