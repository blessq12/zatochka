<?php

namespace App\Domain\OrderFulfillment\ValueObjects;

final readonly class ClientSnapshot
{
    /**
     * @param  array{full_name?: string, phone?: string}  $data
     */
    public function __construct(
        private array $data = [],
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null || $data === []) {
            return null;
        }

        return new self($data);
    }

    public function fullName(): string
    {
        return $this->data['full_name'] ?? '';
    }

    public function phone(): string
    {
        return $this->data['phone'] ?? '';
    }

    /** @return array{full_name?: string, phone?: string} */
    public function toArray(): array
    {
        return $this->data;
    }
}
