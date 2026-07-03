<?php

namespace App\Domain\Equipment\Exception;

use RuntimeException;

final class EquipmentNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("Оборудование #{$id} не найдено.");
    }
}
