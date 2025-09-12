<?php

namespace App\Domain\Order\Exception;

use Exception;

class OrderException extends Exception
{
    public static function orderNotFound(int $id): self
    {
        return new self("Order with id {$id} not found");
    }

    public static function validationFailed(string $message): self
    {
        return new self($message);
    }

    public static function orderAlreadyExists(int $id): self
    {
        return new self("Order with id {$id} already exists");
    }
}
