<?php

namespace App\Infrastructure\Communication\Service;

use App\Domain\Communication\Service\SmsServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService extends AbstractMessageService implements SmsServiceInterface
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.sms.api_url');
        $this->apiKey = config('services.sms.api_key');
    }

    public function send(string $phone, string $message, array $options = []): bool
    {
        if (!$this->validateRecipient($phone)) {
            Log::error('Invalid SMS phone number', ['phone' => $phone]);
            return false;
        }

        $formattedMessage = $this->formatMessage($message);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'phone' => $phone,
                'message' => $formattedMessage,
                'sender' => $options['sender'] ?? config('app.name'),
            ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'phone' => $phone,
                    'message' => $formattedMessage
                ]);
                return true;
            }

            Log::error('Failed to send SMS', [
                'phone' => $phone,
                'response' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('SMS API error', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Валидация номера телефона
     */
    protected function validateRecipient(string $recipient): bool
    {
        // Простая валидация номера телефона
        return preg_match('/^\+?[1-9]\d{1,14}$/', $recipient);
    }
}
