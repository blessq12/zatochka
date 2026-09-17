<?php

namespace App\Application\Identity\DTO;

final readonly class ActorSummary
{
    public function __construct(
        public string $type,
        public int $id,
    ) {}

    /**
     * @return array{type: string, id: int}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
        ];
    }
}
