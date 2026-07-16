<?php

namespace App\Domain\Workshop\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class MasterComment
{
    public function __construct(
        public EntityId $id,
        public EntityId $masterId,
        public string $text,
        public DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {
        if (trim($this->text) === '') {
            throw new DomainException('Master comment cannot be empty.');
        }
    }
}
