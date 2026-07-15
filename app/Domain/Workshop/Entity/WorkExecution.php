<?php

namespace App\Domain\Workshop\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final class WorkExecution
{
    private ?DateTimeImmutable $completedAt = null;

    public function __construct(
        private readonly EntityId $id,
        private readonly string $description,
        private readonly DateTimeImmutable $startedAt,
    ) {
        if (trim($this->description) === '') {
            throw new DomainException('Work description cannot be empty.');
        }
    }

    public static function reconstitute(
        EntityId $id,
        string $description,
        DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt = null,
    ): self {
        $execution = new self($id, $description, $startedAt);
        $execution->completedAt = $completedAt;

        return $execution;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function startedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function completedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function complete(?DateTimeImmutable $completedAt = null): void
    {
        if ($this->completedAt !== null) {
            throw new DomainException('Work execution is already completed.');
        }

        $this->completedAt = $completedAt ?? new DateTimeImmutable();
    }

    public function isCompleted(): bool
    {
        return $this->completedAt !== null;
    }
}
