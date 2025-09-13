<?php

namespace App\Domain\Client\DTO;

readonly class UpdateClientDTO
{
    public function __construct(
        public string $id,
        public ?string $fullName = null,
        public ?string $phone = null,
        public ?string $telegram = null,
        public ?string $birthDate = null,
        public ?string $deliveryAddress = null,
        public ?string $password = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            fullName: $data['full_name'] ?? null,
            phone: $data['phone'] ?? null,
            telegram: $data['telegram'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            password: $data['password'] ?? null
        );
    }

    public function toArray(): array
    {
        $data = ['id' => $this->id];

        if ($this->fullName !== null) $data['full_name'] = $this->fullName;
        if ($this->phone !== null) $data['phone'] = $this->phone;
        if ($this->telegram !== null) $data['telegram'] = $this->telegram;
        if ($this->birthDate !== null) $data['birth_date'] = $this->birthDate;
        if ($this->deliveryAddress !== null) $data['delivery_address'] = $this->deliveryAddress;
        if ($this->password !== null) $data['password'] = $this->password;

        return $data;
    }
}
