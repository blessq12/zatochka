<?php

namespace App\Application\Crm\DTO;

final readonly class ActorResponse
{
    public function __construct(
        public int $id,
        public int $profileAdditionalId,
    ) {}

    /**
     * @return array{id: int, profile_additional_id: int}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'profile_additional_id' => $this->profileAdditionalId,
        ];
    }
}
