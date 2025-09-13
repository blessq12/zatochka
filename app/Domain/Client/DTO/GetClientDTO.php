<?php

namespace App\Domain\Client\DTO;

readonly class GetClientDTO
{
    public function __construct(
        public string $id
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id
        ];
    }
}
