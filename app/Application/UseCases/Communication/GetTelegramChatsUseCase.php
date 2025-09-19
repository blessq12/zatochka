<?php

namespace App\Application\UseCases\Communication;

class GetTelegramChatsUseCase extends BaseCommunicationUseCase
{
    private ?int $clientId = null;
    private ?bool $activeOnly = null;

    protected function validateSpecificData(): void
    {
        $this->clientId = $this->data['client_id'] ?? null;
        $this->activeOnly = $this->data['active_only'] ?? true;

        if (empty($this->clientId)) {
            throw new \InvalidArgumentException('Client ID is required');
        }
    }

    public function execute(): mixed
    {
        $chats = $this->telegramChatRepository->findByClientId($this->clientId, $this->activeOnly);

        return [
            'chats' => $chats,
            'total' => count($chats),
            'active_only' => $this->activeOnly
        ];
    }
}
