<?php

namespace App\Domain\Warehouse\Exception;

use RuntimeException;

final class WarehouseItemNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("Позиция склада #{$id} не найдена.");
    }
}
