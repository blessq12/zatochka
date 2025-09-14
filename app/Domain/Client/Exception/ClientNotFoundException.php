<?php

namespace App\Domain\Client\Exception;

use Exception;

class ClientNotFoundException extends Exception
{
    public static function forId(int $id): self
    {
        return new self("Client with ID {$id} not found.");
    }
}
