<?php

namespace App\Domain\ClientPortal\Entities;

final class SiteLead
{
    /**
     * @param  list<string>  $serviceTypes
     */
    public function __construct(
        private ?int $id,
        private string $fullName,
        private string $phone,
        private ?string $email,
        private array $serviceTypes,
        private ?string $comment,
        private bool $needsDelivery,
        private ?string $deliveryAddress,
        private bool $converted,
        private ?int $orderId,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function fullName(): string
    {
        return $this->fullName;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    /** @return list<string> */
    public function serviceTypes(): array
    {
        return $this->serviceTypes;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function needsDelivery(): bool
    {
        return $this->needsDelivery;
    }

    public function deliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function isConverted(): bool
    {
        return $this->converted;
    }

    public function orderId(): ?int
    {
        return $this->orderId;
    }
}
