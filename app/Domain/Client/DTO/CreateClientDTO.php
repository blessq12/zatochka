<?php

namespace App\Domain\Client\DTO;

readonly class CreateClientDTO
{
    public function __construct(
        public string $fullName,
        public string $phone,
        public ?string $email = null,
        public ?string $telegram = null,
        public ?string $birthDate = null,
        public ?string $deliveryAddress = null,
        public ?string $password = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            fullName: $data['full_name'],
            phone: $data['phone'],
            email: $data['email'] ?? null,
            telegram: $data['telegram'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            password: $data['password'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'phone' => $this->phone,
            'email' => $this->email,
            'telegram' => $this->telegram,
            'birth_date' => $this->birthDate,
            'delivery_address' => $this->deliveryAddress,
            'password' => $this->password,
        ];
    }
}
