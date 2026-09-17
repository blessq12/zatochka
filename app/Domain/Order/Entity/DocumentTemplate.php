<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\Enum\DocumentType;
use DateTimeImmutable;

final class DocumentTemplate
{
    public function __construct(
        private ?int $id,
        private DocumentType $type,
        private string $body,
        private ?int $updatedByIdentityId,
        private ?DateTimeImmutable $updatedAt,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function type(): DocumentType
    {
        return $this->type;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function updatedByIdentityId(): ?int
    {
        return $this->updatedByIdentityId;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function withBody(string $body, ?int $updatedByIdentityId): self
    {
        return new self(
            $this->id,
            $this->type,
            $body,
            $updatedByIdentityId,
            new DateTimeImmutable,
        );
    }
}
