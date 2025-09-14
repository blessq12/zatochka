<?php

namespace App\Domain\Client\Exception;

use Exception;

class ClientPhoneAlreadyExistsException extends Exception
{
    public static function forPhone(string $phone): self
    {
        return new self("Client with phone {$phone} already exists.");
    }
}
