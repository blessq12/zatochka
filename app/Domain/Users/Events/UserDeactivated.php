<?php

namespace App\Domain\Users\Events;

class UserDeactivated
{
    public function __construct(public readonly int $userId) {}
}
