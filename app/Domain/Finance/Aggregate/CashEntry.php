<?php

namespace App\Domain\Finance\Aggregate;

use App\Domain\Finance\CashEntrySource;
use App\Domain\Finance\CashEntryType;
use App\Shared\Domain\DomainException;
use DateTimeImmutable;

final class CashEntry
{
    public function __construct(
        private ?int $id,
        private CashEntryType $type,
        private string $amount,
        private DateTimeImmutable $occurredAt,
        private CashEntrySource $source,
        private ?int $orderId,
        private ?string $comment,
    ) {
        self::assertAmount($amount);
        if ($source === CashEntrySource::OrderIssue && ($orderId === null || $orderId < 1)) {
            throw new DomainException('order_id is required for order_issue cash entry.');
        }
        if ($source === CashEntrySource::Manual && $orderId !== null) {
            throw new DomainException('manual cash entry cannot reference order_id.');
        }
    }

    public static function income(
        string $amount,
        DateTimeImmutable $occurredAt,
        CashEntrySource $source,
        ?int $orderId = null,
        ?string $comment = null,
    ): self {
        return new self(
            null,
            CashEntryType::Income,
            self::normalizeAmount($amount),
            $occurredAt,
            $source,
            $orderId,
            self::normalizeComment($comment),
        );
    }

    public static function expense(
        string $amount,
        DateTimeImmutable $occurredAt,
        ?string $comment = null,
    ): self {
        return new self(
            null,
            CashEntryType::Expense,
            self::normalizeAmount($amount),
            $occurredAt,
            CashEntrySource::Manual,
            null,
            self::normalizeComment($comment),
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function type(): CashEntryType
    {
        return $this->type;
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function source(): CashEntrySource
    {
        return $this->source;
    }

    public function orderId(): ?int
    {
        return $this->orderId;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function isManual(): bool
    {
        return $this->source === CashEntrySource::Manual;
    }

    private static function assertAmount(string $amount): void
    {
        if (! is_numeric($amount) || (float) $amount < 0) {
            throw new DomainException('amount must be a non-negative number.');
        }
    }

    private static function normalizeAmount(string $amount): string
    {
        self::assertAmount($amount);

        return number_format((float) $amount, 2, '.', '');
    }

    private static function normalizeComment(?string $comment): ?string
    {
        if ($comment === null) {
            return null;
        }
        $trimmed = trim($comment);

        return $trimmed === '' ? null : $trimmed;
    }
}
