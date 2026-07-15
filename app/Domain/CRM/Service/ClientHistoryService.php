<?php

namespace App\Domain\CRM\Service;

use App\Domain\CRM\Entity\Client;
use App\Domain\CRM\Entity\ClientHistoryEntry;
use App\Shared\ValueObject\EntityId;

final class ClientHistoryService
{
    public function recordOrder(Client $client, EntityId $historyEntryId, EntityId $orderId, string $note): void
    {
        $client->appendHistory(new ClientHistoryEntry($historyEntryId, $orderId, $note));
    }
}
