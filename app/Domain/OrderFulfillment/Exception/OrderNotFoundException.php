<?php

namespace App\Domain\OrderFulfillment\Exception;

use RuntimeException;

final class OrderNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("Заказ #{$id} не найден.");
    }
}
