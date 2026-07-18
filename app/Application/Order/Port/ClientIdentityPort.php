<?php

namespace App\Application\Order\Port;

/**
 * Cross-BC read/write seam: Order Application resolves a CRM client without importing CRM handlers.
 */
interface ClientIdentityPort
{
    /**
     * Authenticated portal client wins; otherwise find-by-phone or register a new client.
     */
    public function resolveOrRegister(
        ?int $authenticatedClientId,
        string $phone,
        string $fullName,
        ?string $deliveryAddress = null,
    ): int;
}
