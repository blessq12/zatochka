<?php

namespace App\Domain\OrderFulfillment\Entity;

use App\Domain\OrderFulfillment\Enum\DocumentType;

final class DocumentTemplate
{
    public function __construct(
        private ?int $id,
        private DocumentType $type,
        private string $body,
        private ?int $updatedByUserId,
        private ?\DateTimeImmutable $updatedAt,
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

    public function updatedByUserId(): ?int
    {
        return $this->updatedByUserId;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function withBody(string $body, ?int $updatedByUserId): self
    {
        return new self(
            id: $this->id,
            type: $this->type,
            body: $body,
            updatedByUserId: $updatedByUserId,
            updatedAt: new \DateTimeImmutable,
        );
    }
}
