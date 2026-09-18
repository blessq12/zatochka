<?php

namespace App\Domain\Order\Aggregate;

use App\Domain\Order\OrderDraftSource;
use App\Domain\Order\OrderDraftStatus;
use App\Shared\Domain\DomainException;
use DateTimeImmutable;

final class OrderDraft
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private ?int $id,
        private OrderDraftSource $source,
        private OrderDraftStatus $status,
        private ?int $clientId,
        private ?string $fullName,
        private ?string $phone,
        private string $serviceType,
        private array $payload,
        private bool $needsDelivery,
        private ?string $deliveryAddress,
        private ?string $comment,
        private ?int $orderId,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->assertServiceType($serviceType);
        $this->assertDelivery();
        $this->assertSourceInvariants();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function createPublic(
        string $fullName,
        string $phone,
        string $serviceType,
        array $payload,
        bool $needsDelivery,
        ?string $deliveryAddress,
        ?string $comment,
    ): self {
        $fullName = trim($fullName);
        $phone = trim($phone);
        if ($fullName === '' || $phone === '') {
            throw new DomainException('full_name and phone are required.');
        }

        return new self(
            null,
            OrderDraftSource::Public,
            OrderDraftStatus::Pending,
            null,
            $fullName,
            $phone,
            $serviceType,
            $payload,
            $needsDelivery,
            $deliveryAddress,
            self::normalizeComment($comment),
            null,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function createForClient(
        int $clientId,
        string $serviceType,
        array $payload,
        bool $needsDelivery,
        ?string $deliveryAddress,
        ?string $comment,
        ?string $fullName = null,
        ?string $phone = null,
    ): self {
        if ($clientId < 1) {
            throw new DomainException('client_id is required.');
        }

        return new self(
            null,
            OrderDraftSource::ClientLk,
            OrderDraftStatus::Pending,
            $clientId,
            self::normalizeOptionalString($fullName),
            self::normalizeOptionalString($phone),
            $serviceType,
            $payload,
            $needsDelivery,
            $deliveryAddress,
            self::normalizeComment($comment),
            null,
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

    public function source(): OrderDraftSource
    {
        return $this->source;
    }

    public function status(): OrderDraftStatus
    {
        return $this->status;
    }

    public function clientId(): ?int
    {
        return $this->clientId;
    }

    public function fullName(): ?string
    {
        return $this->fullName;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function serviceType(): string
    {
        return $this->serviceType;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    public function needsDelivery(): bool
    {
        return $this->needsDelivery;
    }

    public function deliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function orderId(): ?int
    {
        return $this->orderId;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function syncTimestamps(?DateTimeImmutable $createdAt, ?DateTimeImmutable $updatedAt): void
    {
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updatePending(
        string $serviceType,
        array $payload,
        bool $needsDelivery,
        ?string $deliveryAddress,
        ?string $comment,
        ?string $fullName = null,
        ?string $phone = null,
    ): void {
        $this->assertPending();
        $this->assertServiceType($serviceType);

        if ($this->source === OrderDraftSource::Public) {
            $fullName = trim((string) $fullName);
            $phone = trim((string) $phone);
            if ($fullName === '' || $phone === '') {
                throw new DomainException('full_name and phone are required.');
            }
            $this->fullName = $fullName;
            $this->phone = $phone;
        } else {
            if ($fullName !== null) {
                $this->fullName = self::normalizeOptionalString($fullName);
            }
            if ($phone !== null) {
                $this->phone = self::normalizeOptionalString($phone);
            }
        }

        $this->serviceType = $serviceType;
        $this->payload = $payload;
        $this->needsDelivery = $needsDelivery;
        $this->deliveryAddress = $deliveryAddress;
        $this->comment = self::normalizeComment($comment);
        $this->assertDelivery();
    }

    public function cancel(): void
    {
        $this->assertPending();
        $this->status = OrderDraftStatus::Cancelled;
    }

    public function markPromoted(int $orderId): void
    {
        $this->assertPending();
        if ($orderId < 1) {
            throw new DomainException('order_id is required.');
        }
        $this->status = OrderDraftStatus::Promoted;
        $this->orderId = $orderId;
    }

    private function assertPending(): void
    {
        if (! $this->status->isPending()) {
            throw new DomainException('Order draft can be changed only in pending status.');
        }
    }

    private function assertServiceType(string $serviceType): void
    {
        if (! in_array($serviceType, ['sharpening', 'repair'], true)) {
            throw new DomainException('Invalid service_type.');
        }
    }

    private function assertDelivery(): void
    {
        if ($this->needsDelivery) {
            $address = trim((string) $this->deliveryAddress);
            if ($address === '') {
                throw new DomainException('delivery_address is required when needs_delivery is true.');
            }
            $this->deliveryAddress = $address;
        } else {
            $this->deliveryAddress = null;
        }
    }

    private function assertSourceInvariants(): void
    {
        if ($this->source === OrderDraftSource::Public && $this->clientId !== null) {
            throw new DomainException('Public order draft cannot have client_id.');
        }
        if ($this->source === OrderDraftSource::ClientLk && ($this->clientId === null || $this->clientId < 1)) {
            throw new DomainException('client_id is required for client_lk draft.');
        }
    }

    private static function normalizeComment(?string $comment): ?string
    {
        if ($comment === null) {
            return null;
        }
        $comment = trim($comment);

        return $comment === '' ? null : $comment;
    }

    private static function normalizeOptionalString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
