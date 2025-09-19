<?php

namespace App\Domain\Communication\Service;

interface TelegramServiceInterface
{
    public function send(string $chatId, string $message, array $options = []): bool;
    public function sendWithKeyboard(string $chatId, string $message, array $buttons = []): bool;
}
