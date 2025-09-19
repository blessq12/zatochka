<?php

namespace App\Application\UseCases\Communication;

use Illuminate\Support\Facades\DB;

class SendMessageUseCase extends BaseCommunicationUseCase
{
    private ?int $clientId = null;
    private ?string $message = null;
    private ?string $channel = null;
    private ?string $recipient = null;

    protected function validateSpecificData(): void
    {
        $this->clientId = $this->data['client_id'] ?? null;
        $this->message = $this->data['message'] ?? null;

        if (empty($this->clientId)) {
            throw new \InvalidArgumentException('Client ID is required');
        }

        if (empty($this->message)) {
            throw new \InvalidArgumentException('Message is required');
        }
    }

    public function execute(): mixed
    {
        return DB::transaction(function () {
            $client = $this->clientRepository->findById($this->clientId);
            if (!$client) {
                throw new \InvalidArgumentException('Client not found');
            }

            // Определяем канал и получателя по условиям
            $this->determineChannelAndRecipient($client);

            if (!$this->channel || !$this->recipient) {
                throw new \RuntimeException('No available communication channel for this client');
            }

            // Отправляем сообщение
            $sent = $this->sendMessage();

            if (!$sent) {
                throw new \RuntimeException("Failed to send {$this->channel} message");
            }

            return [
                'success' => true,
                'channel' => $this->channel,
                'recipient' => $this->recipient,
                'message' => $this->message,
                'sent_at' => now()
            ];
        });
    }

    private function determineChannelAndRecipient(Client $client): void
    {
        // Приоритет: Telegram -> SMS -> Email
        // 1. Проверяем активный Telegram чат
        $telegramChat = $this->telegramChatRepository->findByClientId($client->id, true);
        if ($telegramChat && $telegramChat->isVerified()) {
            $this->channel = 'telegram';
            $this->recipient = $telegramChat->chatId;
            return;
        }

        // 2. Проверяем SMS (если есть телефон)
        if (!empty($client->phone)) {
            $this->channel = 'sms';
            $this->recipient = $client->phone;
            return;
        }

        // 3. Email (если есть email)
        if (!empty($client->email)) {
            $this->channel = 'email';
            $this->recipient = $client->email;
            return;
        }
    }

    private function sendMessage(): bool
    {
        switch ($this->channel) {
            case 'telegram':
                return $this->telegramService->send($this->recipient, $this->message);

            case 'sms':
                return $this->smsService->send($this->recipient, $this->message);

            case 'email':
                // TODO: Implement email service
                return true;

            default:
                return false;
        }
    }
}
