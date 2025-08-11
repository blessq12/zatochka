<?php

namespace App\Services;

use App\Contracts\SMSServiceContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSService implements SMSServiceContract
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key');
        $this->apiUrl = config('services.sms.api_url');
    }

    public function sendSMS(string $phone, string $message): bool
    {
        try {
            // Здесь будет интеграция с SMS провайдером
            // Пока заглушка для демонстрации
            Log::info('SMS sent', [
                'phone' => $phone,
                'message' => $message
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendOrderConfirmation(string $phone, string $orderNumber, float $amount): bool
    {
        $message = "Заявка {$orderNumber} подтверждена. Сумма: {$amount} ₽. Спасибо за заказ!";

        return $this->sendSMS($phone, $message);
    }
}
