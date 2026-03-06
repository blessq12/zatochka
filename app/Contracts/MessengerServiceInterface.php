<?php

namespace App\Contracts;

interface MessengerServiceInterface
{
    /**
     * Отправить сообщение получателю.
     *
     * @param string $recipientId Идентификатор получателя (chat_id для Telegram, user_id для MAX)
     * @param string $text Текст сообщения
     * @param array{with_keyboard?: bool} $options Дополнительные опции
     */
    public function send(string $recipientId, string $text, array $options = []): void;
}
