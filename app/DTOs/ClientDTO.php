<?php

namespace App\DTOs;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ClientDTO extends BaseDTO
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $phone,
        public readonly ?string $telegram = null,
        public readonly ?string $birthDate = null,
        public readonly ?string $deliveryAddress = null,
        public readonly ?string $password = null
    ) {
        if (!$this->validate()) {
            throw new ValidationException(Validator::make([], []));
        }
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'phone' => $this->phone,
            'telegram' => $this->telegram,
            'birth_date' => $this->birthDate,
            'delivery_address' => $this->deliveryAddress,
            'password' => $this->password,
        ];
    }

    /**
     * Validate DTO data
     */
    public function validate(): bool
    {
        $validator = Validator::make($this->toArray(), static::rules());
        return !$validator->fails();
    }

    /**
     * Get validation rules
     */
    public static function rules(): array
    {
        return [
            'full_name' => 'required|string|min:2|max:255',
            'phone' => 'required|string|min:10|max:20',
            'telegram' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'delivery_address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:6',
        ];
    }

    /**
     * Create from request data
     */
    public static function fromRequest(array $data): static
    {
        return new static(
            fullName: $data['full_name'],
            phone: $data['phone'],
            telegram: $data['telegram'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            password: $data['password'] ?? null
        );
    }

    /**
     * Create registration DTO
     */
    public static function fromRegistrationRequest(array $data): static
    {
        return new static(
            fullName: $data['full_name'],
            phone: $data['phone'],
            telegram: $data['telegram'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            password: $data['password']
        );
    }

    /**
     * Create login DTO
     */
    public static function fromLoginRequest(array $data): static
    {
        return new static(
            fullName: '', // Не нужно для логина
            phone: $data['phone'],
            password: $data['password']
        );
    }
}
