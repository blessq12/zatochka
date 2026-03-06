<?php

namespace App\Services\Messenger;

use App\Contracts\MessengerServiceInterface;
use Illuminate\Support\Facades\Log;

class MaxMessengerService implements MessengerServiceInterface
{
    private const API_BASE = 'https://platform-api.max.ru';

    public function send(string $recipientId, string $text, array $options = []): void
    {
        $token = config('services.max.bot_token');

        if (!$token) {
            Log::warning('MAX bot token not configured');
            return;
        }

        $url = self::API_BASE . '/messages?user_id=' . (int) $recipientId;

        $body = [
            'text' => $text,
            'format' => 'html',
        ];

        if ($options['with_keyboard'] ?? false) {
            $body['attachments'] = [
                [
                    'type' => 'inline_keyboard',
                    'payload' => [
                        'buttons' => [
                            [['type' => 'message', 'text' => '👤 Аккаунт']],
                            [
                                ['type' => 'message', 'text' => '📋 Активные заказы'],
                                ['type' => 'message', 'text' => '📚 История заказов'],
                            ],
                        ],
                    ],
                ],
            ];
        }

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $token,
                    'Content-Type: application/json',
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Log::error('MAX send message failed', [
                    'http_code' => $httpCode,
                    'response' => $response,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('MAX send message exception: ' . $e->getMessage());
        }
    }
}
