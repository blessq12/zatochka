<?php

namespace App\Application\Identity\DTO;

final readonly class IdentityResponse
{
    public function __construct(
        public int $id,
        public string $email,
        public ActorSummary $actor,
        public ?string $token = null,
    ) {}

    /**
     * @return array{id: int, email: string, actor: array{type: string, id: int}, token?: string}
     */
    public function toArray(): array
    {
        $payload = [
            'id' => $this->id,
            'email' => $this->email,
            'actor' => $this->actor->toArray(),
        ];

        if ($this->token !== null) {
            $payload['token'] = $this->token;
        }

        return $payload;
    }
}
