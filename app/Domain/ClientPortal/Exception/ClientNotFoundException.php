<?php

namespace App\Domain\ClientPortal\Exception;

use RuntimeException;

final class ClientNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("Клиент #{$id} не найден.");
    }
}
