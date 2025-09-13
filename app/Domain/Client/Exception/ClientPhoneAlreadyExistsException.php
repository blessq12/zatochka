<?php

namespace App\Domain\Client\Exception;

class ClientPhoneAlreadyExistsException extends ClientException
{
    public static function forPhone(string $phone): self
    {
        return new self("Клиент с телефоном {$phone} уже существует");
    }
}
