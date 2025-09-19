<?php

namespace App\Application\UseCases\Communication\Telegram\Verification;

use App\Application\UseCases\Communication\BaseCommunicationUseCase;
use Illuminate\Support\Facades\Log;

class VerifyTelegramCodeUseCase extends BaseCommunicationUseCase
{
    protected function validateSpecificData(): void
    {
        if (!$this->authContext) {
            throw new \Exception('Client not authenticated');
        }

        if (!isset($this->data['code'])) {
            throw new \Exception('Verification code is required');
        }

        if (empty(trim($this->data['code']))) {
            throw new \Exception('Verification code cannot be empty');
        }

        if (!$this->authContext->telegram || empty(trim($this->authContext->telegram))) {
            throw new \Exception('Telegram username not found for client');
        }
    }

    public function execute(): mixed
    {
        $telegramUsername = trim($this->authContext->telegram);
        $providedCode = trim($this->data['code']);

        // Получаем сохраненный код из кэша
        $storedCode = $this->getVerificationCode($telegramUsername);

        if (!$storedCode) {
            throw new \Exception('Код подтверждения не найден или истек. Запросите новый код.');
        }

        // Проверяем соответствие кода
        if ($storedCode !== $providedCode) {
            Log::warning('Invalid Telegram verification code attempt', [
                'client_id' => $this->authContext->id,
                'telegram_username' => $telegramUsername,
                'provided_code' => $providedCode,
                'stored_code' => $storedCode,
            ]);

            throw new \Exception('Неверный код подтверждения. Проверьте код и попробуйте снова.');
        }

        // Код верный - обновляем клиента
        $this->updateClientVerification($this->authContext->id);

        // Удаляем код из кэша
        $this->clearVerificationCode($telegramUsername);

        Log::info('Telegram verification successful', [
            'client_id' => $this->authContext->id,
            'telegram_username' => $telegramUsername,
            'verified_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Telegram успешно подтвержден',
            'telegram_username' => $telegramUsername,
            'verified_at' => now()->toISOString(),
        ];
    }

    /**
     * Обновляет дату подтверждения Telegram у клиента
     */
    private function updateClientVerification(int $clientId): void
    {
        $this->clientRepository->updateTelegramVerification((string) $clientId, now());
    }
}
