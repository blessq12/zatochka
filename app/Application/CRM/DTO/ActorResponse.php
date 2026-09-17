<?php

namespace App\Application\Crm\DTO;

final readonly class ActorResponse
{
    public function __construct(
        public int $id,
        public string $type,
        public ?string $email,
        public int $profileAdditionalId,
        public ?string $name,
        public ?string $phone,
        public ?string $birthday,
    ) {}

    /**
     * @return array{
     *     id: int,
     *     type: string,
     *     email: string|null,
     *     profile_additional_id: int,
     *     name: string|null,
     *     phone: string|null,
     *     birthday: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'email' => $this->email,
            'profile_additional_id' => $this->profileAdditionalId,
            'name' => $this->name,
            'phone' => $this->phone,
            'birthday' => $this->birthday,
        ];
    }
}
