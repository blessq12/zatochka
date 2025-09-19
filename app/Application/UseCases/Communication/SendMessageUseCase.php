<?php

namespace App\Application\UseCases\Communication;

use App\Domain\Communication\Service\TelegramServiceInterface;
use App\Domain\Communication\Service\SmsServiceInterface;
use Illuminate\Support\Facades\DB;

class SendMessageUseCase extends BaseCommunicationUseCase
{
    private ?string $channel = null;
    private ?string $recipient = null;
    private ?string $message = null;
    private ?array $options = null;

    protected function validateSpecificData(): void
    {
        $this->channel = $this->data['channel'] ?? null;
        $this->recipient = $this->data['recipient'] ?? null;
        $this->message = $this->data['message'] ?? null;
        $this->options = $this->data['options'] ?? [];

        if (empty($this->channel)) {
            throw new \InvalidArgumentException('Channel is required');
        }

        if (!in_array($this->channel, ['telegram', 'sms'])) {
            throw new \InvalidArgumentException('Invalid channel. Must be telegram or sms');
        }

        if (empty($this->recipient)) {
            throw new \InvalidArgumentException('Recipient is required');
        }

        if (empty($this->message)) {
            throw new \InvalidArgumentException('Message is required');
        }

        // Валидация длины сообщения в зависимости от канала
        if ($this->channel === 'telegram' && strlen($this->message) > 4096) {
            throw new \InvalidArgumentException('Telegram message is too long (max 4096 characters)');
        }

        if ($this->channel === 'sms' && strlen($this->message) > 160) {
            throw new \InvalidArgumentException('SMS message is too long (max 160 characters)');
        }
    }

    public function execute(): mixed
    {
        return DB::transaction(function () {
            $sent = false;
            $service = null;

            // Выбираем сервис в зависимости от канала
            switch ($this->channel) {
                case 'telegram':
                    $service = app(TelegramServiceInterface::class);
                    $sent = $service->send($this->recipient, $this->message, $this->options);
                    break;

                case 'sms':
                    $service = app(SmsServiceInterface::class);
                    $sent = $service->send($this->recipient, $this->message, $this->options);
                    break;
            }

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
}
