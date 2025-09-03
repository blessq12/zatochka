<?php

namespace App\Domain\Users\Events;

class UserDeleted
{
    public function __construct(public readonly int $userId)
    {
    }
}
