<?php

declare(strict_types=1);

namespace App\Domain\Bonuses;

final class BonusTransaction
{
    private int $id;
    private int $accountId;
    private string $type;
    private BonusAmount $amount;
    private ?int $orderId;
    private ?string $relatedTransactionId;
    private string $idempotencyKey;
    private \DateTimeImmutable $occurredAt;

    private function __construct(
        int $id,
        int $accountId,
        string $type,
        BonusAmount $amount,
        ?int $orderId,
        ?string $relatedTransactionId,
        string $idempotencyKey,
        \DateTimeImmutable $occurredAt
    ) {
        BonusTransactionType::assertValid($type);
        $this->id = $id;
        $this->accountId = $accountId;
        $this->type = $type;
        $this->amount = $amount;
        $this->orderId = $orderId;
        $this->relatedTransactionId = $relatedTransactionId;
        $this->idempotencyKey = $idempotencyKey;
        $this->occurredAt = $occurredAt;
    }

    public static function create(
        int $accountId,
        string $type,
        BonusAmount $amount,
        ?int $orderId,
        ?string $relatedTransactionId,
        string $idempotencyKey
    ): self {
        return new self(
            id: 0, // Временный ID, будет заменен при сохранении
            accountId: $accountId,
            type: $type,
            amount: $amount,
            orderId: $orderId,
            relatedTransactionId: $relatedTransactionId,
            idempotencyKey: $idempotencyKey,
            occurredAt: new \DateTimeImmutable('now')
        );
    }

    public static function reconstitute(
        int $id,
        int $accountId,
        string $type,
        BonusAmount $amount,
        ?int $orderId,
        ?string $relatedTransactionId,
        string $idempotencyKey,
        \DateTimeImmutable $occurredAt
    ): self {
        return new self(
            $id,
            $accountId,
            $type,
            $amount,
            $orderId,
            $relatedTransactionId,
            $idempotencyKey,
            $occurredAt
        );
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getAccountId(): int
    {
        return $this->accountId;
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function getAmount(): BonusAmount
    {
        return $this->amount;
    }
    public function getOrderId(): ?int
    {
        return $this->orderId;
    }
    public function getRelatedTransactionId(): ?string
    {
        return $this->relatedTransactionId;
    }
    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }
    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
