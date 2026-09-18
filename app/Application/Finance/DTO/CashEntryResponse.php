<?php

namespace App\Application\Finance\DTO;

final readonly class CashEntryResponse
{
    public function __construct(
        public int $id,
        public string $type,
        public string $amount,
        public string $occurredAt,
        public string $source,
        public ?int $orderId,
        public ?string $comment,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'occurred_at' => $this->occurredAt,
            'source' => $this->source,
            'order_id' => $this->orderId,
            'comment' => $this->comment,
        ];
    }
}
