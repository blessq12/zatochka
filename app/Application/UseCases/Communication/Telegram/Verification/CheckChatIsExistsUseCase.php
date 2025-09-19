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
        
        return $telegramChat ? 1 : 0;
    }
}
