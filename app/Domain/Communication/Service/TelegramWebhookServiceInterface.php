<?php

namespace App\Domain\Communication\Service;

interface TelegramWebhookServiceInterface
{
    public function handleWebhook(array $data): array;
    public function sendVerificationCode(string $chatId, string $code): bool;
    public function sendWelcomeMessage(string $chatId): bool;
}
