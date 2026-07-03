<?php

namespace App\Domain\Identity\Exception;

use RuntimeException;

final class MasterAlreadyExistsException extends RuntimeException
{
    public static function withEmail(string $email): self
    {
        return new self("Пользователь с email {$email} уже существует.");
    }
}
