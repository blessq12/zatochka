<?php

namespace App\Contracts;

interface TelegramWebhookServiceContract
{
    /**
     * Обработать входящий webhook от Telegram
     */
    public function handleWebhook(array $data): void;

    /**
     * Установить webhook URL для бота
     */
    public function setWebhook(string $webhookUrl): array;

    /**
     * Получить информацию о webhook
     */
    public function getWebhookInfo(): array;

    /**
     * Удалить webhook
     */
    public function deleteWebhook(): array;

    /**
     * Отправить тестовое сообщение
     */
    public function sendTestMessage(int $chatId, string $message): bool;
}
