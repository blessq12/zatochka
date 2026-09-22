<?php

namespace App\Domain\Order\Aggregate;

use App\Domain\Order\OrderCommentKind;
use App\Shared\Domain\DomainException;
use DateTimeImmutable;

final class OrderComment
{
    public function __construct(
        private ?int $id,
        private int $orderId,
        private string $authorType,
        private int $authorId,
        private string $body,
        private OrderCommentKind $kind = OrderCommentKind::Regular,
        private ?DateTimeImmutable $createdAt = null,
    ) {
        $this->assertAuthorType($authorType);
        $this->assertBody($body);
    }

    public static function create(
        int $orderId,
        string $authorType,
        int $authorId,
        string $body,
        OrderCommentKind $kind = OrderCommentKind::Regular,
    ): self {
        if ($orderId < 1) {
            throw new DomainException('order_id is required.');
        }
        if ($authorId < 1) {
            throw new DomainException('author_id is required.');
        }

        return new self(
            null,
            $orderId,
            $authorType,
            $authorId,
            self::normalizeBody($body),
            $kind,
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

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function authorType(): string
    {
        return $this->authorType;
    }

    public function authorId(): int
    {
        return $this->authorId;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function kind(): OrderCommentKind
    {
        return $this->kind;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function syncCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    private function assertAuthorType(string $authorType): void
    {
        if (! in_array($authorType, ['managers', 'masters'], true)) {
            throw new DomainException('author_type must be managers or masters.');
        }
    }

    private function assertBody(string $body): void
    {
        if (trim($body) === '') {
            throw new DomainException('Comment body is required.');
        }
    }

    private static function normalizeBody(string $body): string
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            throw new DomainException('Comment body is required.');
        }

        return $trimmed;
    }
}
