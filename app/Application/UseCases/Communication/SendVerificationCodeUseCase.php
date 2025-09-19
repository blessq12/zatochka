<?php

namespace App\Application\UseCases\Communication;

class SendVerificationCodeUseCase extends BaseCommunicationUseCase
{
    /**
     * @var \App\Models\User
     * $authContext is auth via sanctum client
     */
    public function validateSpecificData(): void
    {
        if (!$this->authContext) {
            throw new \Exception('Client not authenticated');
        }

        if (!$this->authContext->telegram || empty(trim($this->authContext->telegram))) {
            throw new \Exception('Client telegram username not specified');
        }
    }

    public function execute(): mixed
    {
        return [
            'success' => true,
            'message' => 'Verification code sent successfully',
            'client' => $this->authContext,
        ];
    }
}
