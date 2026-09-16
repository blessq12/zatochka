<?php

namespace App\Domain\CRM\ValueObject;

final readonly class AdditionalData
{
    public function __construct(
        private ?string $birthDate = null,
        private ?string $deliveryAddress = null,
    ) {}

    public static function empty(): self
    {
        return new self();
    }

    public function birthDate(): ?string
    {
        return $this->birthDate;
    }

    public function deliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function withBirthDate(?string $birthDate): self
    {
        $normalized = $birthDate !== null && trim($birthDate) !== '' ? trim($birthDate) : null;

        return new self($normalized, $this->deliveryAddress);
    }

    public function withDeliveryAddress(?string $deliveryAddress): self
    {
        $normalized = $deliveryAddress !== null ? trim($deliveryAddress) : null;
        $normalized = $normalized !== '' ? $normalized : null;

        return new self($this->birthDate, $normalized);
    }

    public function equals(self $other): bool
    {
        return $this->birthDate === $other->birthDate
            && $this->deliveryAddress === $other->deliveryAddress;
    }
}
