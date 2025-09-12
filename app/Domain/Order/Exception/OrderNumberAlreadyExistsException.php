<?php

namespace App\Domain\Order\Exception;

use Exception;

class OrderNumberAlreadyExistsException extends Exception
{
    public static function numberExists(string $orderNumber): self
    {
        return new self("Номер заказа '{$orderNumber}' уже существует");
    }

    public static function generationFailed(string $reason = ''): self
    {
        $message = 'Не удалось сгенерировать уникальный номер заказа';
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($message);
    }
}
