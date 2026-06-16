<?php

namespace App\Domain\ClientPortal\Entity;

use App\Domain\ClientPortal\Exception\SiteLeadPolicyViolation;

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

    public function assignId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    /**
     * @param  list<string>  $serviceTypes
     */
    public static function create(
        string $fullName,
        string $phone,
        array $serviceTypes,
        ?string $email = null,
        ?string $comment = null,
        bool $needsDelivery = false,
        ?string $deliveryAddress = null,
    ): self {
        return new self(
            id: null,
            fullName: $fullName,
            phone: $phone,
            email: $email,
            serviceTypes: $serviceTypes,
            comment: $comment,
            needsDelivery: $needsDelivery,
            deliveryAddress: $deliveryAddress,
            converted: false,
            orderId: null,
        );
    }

    /** POL-09: конвертация один раз. */
    public function markConverted(int $orderId): self
    {
        if ($this->converted) {
            throw new SiteLeadPolicyViolation('Заявка уже конвертирована в заказ.');
        }

        $clone = clone $this;
        $clone->converted = true;
        $clone->orderId = $orderId;

        return $clone;
    }
}
