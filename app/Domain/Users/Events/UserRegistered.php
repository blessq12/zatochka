<?php

namespace App\Domain\Users\Events;

use App\Domain\Users\ValueObjects\Email;
use App\Domain\Users\ValueObjects\UserId;

class UserRegistered
{
    public function __construct(
        public readonly UserId $userId,
        public readonly string $name,
        public readonly Email $email
    ) {}
}
