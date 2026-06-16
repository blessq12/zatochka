<?php

namespace App\Domain\ClientPortal\Exception;

use DomainException;

final class ClientAlreadyRegisteredException extends DomainException
{
    public static function forPhone(string $phone): self
    {
        return new self("Клиент с телефоном {$phone} уже зарегистрирован.");
    }
}
