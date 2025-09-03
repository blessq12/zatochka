<?php

namespace App\Domain\Users\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Users\ValueObjects\Email;

class UserRegistered extends DomainEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly Email $email
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'UserRegistered';
    }

    public function eventData(): array
    {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'email' => (string) $this->email,
        ];
    }
}
