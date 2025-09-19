<?php

namespace App\Domain\Communication\Service;

interface TelegramMessageServiceInterface
{
    /**
     * Отправить текстовое сообщение в Telegram чат
     */
    public function sendMessage(int $chatId, string $text, ?array $options = null): array;

    /**
     * Отправить сообщение с клавиатурой
     */
    public function sendMessageWithKeyboard(int $chatId, string $text, array $keyboard): array;

    /**
     * Отправить сообщение с inline клавиатурой
     */
    public function sendMessageWithInlineKeyboard(int $chatId, string $text, array $inlineKeyboard): array;

    /**
     * Редактировать существующее сообщение
     */
    public function editMessage(int $chatId, int $messageId, string $text, ?array $options = null): array;

    /**
     * Удалить сообщение
     */
    public function deleteMessage(int $chatId, int $messageId): array;

    /**
     * Отправить фото с подписью
     */
    public function sendPhoto(int $chatId, string $photo, ?string $caption = null, ?array $options = null): array;

    /**
     * Отправить документ
     */
    public function sendDocument(int $chatId, string $document, ?string $caption = null, ?array $options = null): array;

    /**
     * Получить информацию о чате
     */
    public function getChat(int $chatId): array;

    /**
     * Получить информацию о боте
     */
    public function getMe(): array;
}
