<?php

namespace App\DTOs;

abstract class BaseDTO
{
    /**
     * Create DTO from array
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    /**
     * Convert DTO to array
     */
    abstract public function toArray(): array;

    /**
     * Validate DTO data
     */
    abstract public function validate(): bool;

    /**
     * Get validation rules
     */
    abstract public static function rules(): array;
}
