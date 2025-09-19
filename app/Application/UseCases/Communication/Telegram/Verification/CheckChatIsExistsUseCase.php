<?php

namespace App\Application\UseCases\Communication\Telegram\Verification;

use App\Application\UseCases\Communication\BaseCommunicationUseCase;

class CheckChatIsExistsUseCase extends BaseCommunicationUseCase
{
    protected function validateSpecificData(): void
    {
        //
    }

    public function execute(): mixed
    {
        $telegramUsername = trim($this->authContext->telegram);

        if (!$telegramUsername) {
            return 0;
        }

        $telegramChat = $this->findTelegramChatByUsername($telegramUsername);

        if ($telegramChat) {
            $this->linkChatToClient($telegramChat, $this->authContext->id);
            return 1;
        }

        return 0;
    }


    private function linkChatToClient($telegramChat, string $clientId): void
    {
        $this->telegramChatRepository->update($telegramChat->getId(), [
            'client_id' => $clientId,
            'is_active' => true,
        ]);
    }
}
