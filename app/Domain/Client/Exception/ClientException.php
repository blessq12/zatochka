<?php

namespace App\Domain\Client\Exception;

use Exception;

class ClientException extends Exception
{
    public static function clientNotFound(string $id): self
    {
        return new self("Клиент с ID {$id} не найден");
    }

    public static function phoneAlreadyExists(string $phone): self
    {
        return new self("Клиент с телефоном {$phone} уже существует");
    }
}
