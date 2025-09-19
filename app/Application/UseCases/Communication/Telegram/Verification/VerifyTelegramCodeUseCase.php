<?php

namespace App\Application\UseCases\Communication\Telegram\Verification;

use App\Application\UseCases\Communication\BaseCommunicationUseCase;

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
        $storedCode = $this->getVerificationCode($telegramUsername);

        if (!$storedCode) {
            throw new \Exception('Код подтверждения не найден или истек. Запросите новый код.');
        }

        if ($storedCode !== $providedCode) {
            throw new \Exception('Неверный код подтверждения. Проверьте код и попробуйте снова.');
        }

        $this->ensureChatLinkedToClient($telegramUsername);

        $updatedClient = $this->updateClientVerification($this->authContext->id);

        $this->clearVerificationCode($telegramUsername);

        return [
            'success' => true,
            'message' => 'Telegram успешно подтвержден',
            'telegram_username' => $telegramUsername,
            'verified_at' => now()->toISOString(),
            'client' => $updatedClient,
        ];
    }

    /**
     * Обеспечивает привязку чата к клиенту
     */
    private function ensureChatLinkedToClient(string $telegramUsername): void
    {
        $existingChat = $this->telegramChatRepository->findByClientId($this->authContext->id);

        if ($existingChat) {
            return;
        }

        $telegramChat = $this->findTelegramChatByUsername($telegramUsername);

        if ($telegramChat) {
            $this->telegramChatRepository->update($telegramChat->getId(), [
                'client_id' => $this->authContext->id,
                'is_active' => true,
            ]);
        }
    }


    /**
     * Обновляет дату подтверждения Telegram у клиента
     */
    private function updateClientVerification(int $clientId): mixed
    {
        return $this->clientRepository->updateTelegramVerification((string) $clientId, now());
    }
}
