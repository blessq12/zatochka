<?php

namespace App\Domain\Users\Events;

use App\Domain\Users\ValueObjects\UserId;

class UserDeleted
{
    public function __construct(public readonly UserId $userId) {}
}
