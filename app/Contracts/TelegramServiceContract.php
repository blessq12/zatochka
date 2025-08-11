<?php

namespace App\Contracts;

interface TelegramServiceContract
{
    public function sendMessage(string $chatId, string $message): bool;

    public function sendOrderConfirmation(string $telegramUsername, string $orderNumber, float $amount): bool;
}
