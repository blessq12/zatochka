<?php

namespace App\Domain\Identity\Exception;

use RuntimeException;

final class MasterNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("Пользователь #{$id} не найден.");
    }
}
