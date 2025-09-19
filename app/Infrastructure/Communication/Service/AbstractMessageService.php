<?php

namespace App\Infrastructure\Communication\Service;

abstract class AbstractMessageService
{
    /**
     * Валидация получателя
     */
    protected function validateRecipient(string $recipient): bool
    {
        return !empty($recipient);
    }

    /**
     * Форматирование сообщения
     */
    protected function formatMessage(string $message): string
    {
        return trim($message);
    }
}
