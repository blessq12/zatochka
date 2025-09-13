<?php

namespace App\Domain\Client\Exception;

class ClientNotFoundException extends ClientException
{
    public static function forId(string $id): self
    {
        return new self("Клиент с ID {$id} не найден");
    }
}
