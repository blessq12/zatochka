<?php

namespace App\Domain\Workshop\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final class Diagnosis
{
    public function __construct(
        private readonly EntityId $id,
        private readonly string $summary,
        private readonly DateTimeImmutable $completedAt,
        private readonly ?string $technicalNotes = null,
    ) {
        if (trim($this->summary) === '') {
            throw new DomainException('Diagnosis summary cannot be empty.');
        }
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function technicalNotes(): ?string
    {
        return $this->technicalNotes;
    }

    public function completedAt(): DateTimeImmutable
    {
        return $this->completedAt;
    }
}
