<?php

namespace App\Domain\Users\Events;

class UserActivated
{
    public function __construct(public readonly int $userId)
    {
    }
}
