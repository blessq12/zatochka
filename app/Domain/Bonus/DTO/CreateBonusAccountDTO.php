<?php

namespace App\Domain\Bonus\DTO;

use Spatie\LaravelData\Data;

class CreateBonusAccountDTO extends Data
{
    public function __construct(
        public readonly int $clientId
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            clientId: $data['client_id']
        );
    }

    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
        ];
    }
}
