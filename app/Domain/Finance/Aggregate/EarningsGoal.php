<?php

namespace App\Domain\Finance\Aggregate;

use App\Domain\Finance\EarningsGoalStatus;
use App\Shared\Domain\DomainException;
use DateTimeImmutable;

final class EarningsGoal
{
    public function __construct(
        private ?int $id,
        private string $targetAmount,
        private DateTimeImmutable $startsAt,
        private DateTimeImmutable $endsAt,
        private EarningsGoalStatus $status,
        private ?string $title = null,
    ) {
        self::assertTarget($targetAmount);
        if ($endsAt < $startsAt) {
            throw new DomainException('ends_at must be >= starts_at.');
        }
    }

    public static function create(
        string $targetAmount,
        DateTimeImmutable $startsAt,
        DateTimeImmutable $endsAt,
        ?string $title = null,
    ): self {
        $starts = $startsAt->setTime(0, 0, 0);
        $ends = $endsAt->setTime(23, 59, 59);

        return new self(
            null,
            self::normalizeAmount($targetAmount),
            $starts,
            $ends,
            EarningsGoalStatus::Active,
            self::normalizeTitle($title),
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

    public function targetAmount(): string
    {
        return $this->targetAmount;
    }

    public function startsAt(): DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function endsAt(): DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function status(): EarningsGoalStatus
    {
        return $this->status;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function cancel(): void
    {
        if ($this->status === EarningsGoalStatus::Cancelled) {
            return;
        }
        $this->status = EarningsGoalStatus::Cancelled;
    }

    private static function assertTarget(string $amount): void
    {
        if (! is_numeric($amount) || (float) $amount <= 0) {
            throw new DomainException('target_amount must be a positive number.');
        }
    }

    private static function normalizeAmount(string $amount): string
    {
        self::assertTarget($amount);

        return number_format((float) $amount, 2, '.', '');
    }

    private static function normalizeTitle(?string $title): ?string
    {
        if ($title === null) {
            return null;
        }
        $trimmed = trim($title);

        return $trimmed === '' ? null : $trimmed;
    }
}
