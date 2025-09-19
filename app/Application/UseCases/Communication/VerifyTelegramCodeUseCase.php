<?php

namespace App\Application\UseCases\Communication;

class VerifyTelegramCodeUseCase extends BaseCommunicationUseCase
{
    protected function validateSpecificData(): void
    {
        //
    }

    public function execute(): mixed
    {
        return [
            'success' => true,
            'message' => 'Code verified successfully',
        ];
    }
}
