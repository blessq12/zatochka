<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Aggregate\OrderDraft;

interface OrderDraftRepository
{
    public function save(OrderDraft $draft): OrderDraft;

    public function findById(int $id): ?OrderDraft;

    /**
     * @param  list<string>|null  $statusesIn
     * @return list<OrderDraft>
     */
    public function all(
        ?int $clientId = null,
        ?string $status = null,
        ?string $source = null,
        ?string $phone = null,
        ?array $statusesIn = null,
    ): array;
}
