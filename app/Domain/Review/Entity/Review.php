<?php

namespace App\Domain\Review\Entity;

readonly class Review
{
    public function __construct(
        public ?int $id,
        public int $clientId,
        public int $orderId,
        public int $rating,
        public string $comment,
        public bool $isApproved = false,
        public ?string $reply = null,
        public array $metadata = [],
        public bool $isDeleted = false,
        public ?\DateTime $createdAt = null,
        public ?\DateTime $updatedAt = null,
    ) {}

    // Getters (теперь свойства публичные)
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function isApproved(): bool
    {
        return $this->isApproved;
    }

    public function getReply(): ?string
    {
        return $this->reply;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    // Business methods
    public function isHighRating(): bool
    {
        return $this->rating >= 4;
    }

    public function isLowRating(): bool
    {
        return $this->rating <= 2;
    }

    public function hasReply(): bool
    {
        return !empty($this->reply);
    }

    public function isActive(): bool
    {
        return !$this->isDeleted && $this->isApproved;
    }

    public function getRatingStars(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    // Mutators (создают новые экземпляры)
    public function approve(): self
    {
        return new self(
            id: $this->id,
            clientId: $this->clientId,
            orderId: $this->orderId,
            rating: $this->rating,
            comment: $this->comment,
            isApproved: true,
            reply: $this->reply,
            metadata: $this->metadata,
            isDeleted: $this->isDeleted,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function reject(): self
    {
        return new self(
            id: $this->id,
            clientId: $this->clientId,
            orderId: $this->orderId,
            rating: $this->rating,
            comment: $this->comment,
            isApproved: false,
            reply: $this->reply,
            metadata: $this->metadata,
            isDeleted: $this->isDeleted,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function addReply(string $reply): self
    {
        return new self(
            id: $this->id,
            clientId: $this->clientId,
            orderId: $this->orderId,
            rating: $this->rating,
            comment: $this->comment,
            isApproved: $this->isApproved,
            reply: $reply,
            metadata: $this->metadata,
            isDeleted: $this->isDeleted,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function updateRating(int $rating): self
    {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5');
        }

        return new self(
            id: $this->id,
            clientId: $this->clientId,
            orderId: $this->orderId,
            rating: $rating,
            comment: $this->comment,
            isApproved: $this->isApproved,
            reply: $this->reply,
            metadata: $this->metadata,
            isDeleted: $this->isDeleted,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function updateComment(string $comment): self
    {
        return new self(
            id: $this->id,
            clientId: $this->clientId,
            orderId: $this->orderId,
            rating: $this->rating,
            comment: $comment,
            isApproved: $this->isApproved,
            reply: $this->reply,
            metadata: $this->metadata,
            isDeleted: $this->isDeleted,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function addMetadata(string $key, mixed $value): self
    {
        $metadata = $this->metadata;
        $metadata[$key] = $value;

        return new self(
            id: $this->id,
            clientId: $this->clientId,
            orderId: $this->orderId,
            rating: $this->rating,
            comment: $this->comment,
            isApproved: $this->isApproved,
            reply: $this->reply,
            metadata: $metadata,
            isDeleted: $this->isDeleted,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function softDelete(): self
    {
        return new self(
            id: $this->id,
            clientId: $this->clientId,
            orderId: $this->orderId,
            rating: $this->rating,
            comment: $this->comment,
            isApproved: $this->isApproved,
            reply: $this->reply,
            metadata: $this->metadata,
            isDeleted: true,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function restore(): self
    {
        return new self(
            id: $this->id,
            clientId: $this->clientId,
            orderId: $this->orderId,
            rating: $this->rating,
            comment: $this->comment,
            isApproved: $this->isApproved,
            reply: $this->reply,
            metadata: $this->metadata,
            isDeleted: false,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    // Factory method
    public static function create(
        int $clientId,
        int $orderId,
        int $rating,
        string $comment,
        array $metadata = []
    ): self {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5');
        }

        return new self(
            id: null,
            clientId: $clientId,
            orderId: $orderId,
            rating: $rating,
            comment: $comment,
            metadata: $metadata
        );
    }

    // Array conversion
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'order_id' => $this->orderId,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_approved' => $this->isApproved,
            'reply' => $this->reply,
            'metadata' => $this->metadata,
            'is_deleted' => $this->isDeleted,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    // Magic methods for Filament compatibility
    public function __get(string $name): mixed
    {
        return match ($name) {
            'id' => $this->id,
            'client_id' => $this->clientId,
            'order_id' => $this->orderId,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_approved' => $this->isApproved,
            'reply' => $this->reply,
            'metadata' => $this->metadata,
            'is_deleted' => $this->isDeleted,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            default => throw new \InvalidArgumentException("Property {$name} does not exist"),
        };
    }

    public function __isset(string $name): bool
    {
        return in_array($name, [
            'id',
            'client_id',
            'order_id',
            'rating',
            'comment',
            'is_approved',
            'reply',
            'metadata',
            'is_deleted',
            'created_at',
            'updated_at'
        ]);
    }
}
