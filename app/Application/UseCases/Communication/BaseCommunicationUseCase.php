<?php

namespace App\Application\UseCases\Communication;

use App\Application\UseCases\UseCaseInterface;
use App\Domain\Communication\Repository\NotificationRepository;
use App\Domain\Communication\Repository\TelegramChatRepository;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Communication\Service\TelegramServiceInterface;
use App\Domain\Communication\Service\SmsServiceInterface;
use App\Domain\Communication\Service\TelegramWebhookServiceInterface;

abstract class BaseCommunicationUseCase implements UseCaseInterface
{
    protected NotificationRepository $notificationRepository;
    protected TelegramChatRepository $telegramChatRepository;
    protected ClientRepository $clientRepository;
    protected TelegramServiceInterface $telegramService;
    protected SmsServiceInterface $smsService;
    protected TelegramWebhookServiceInterface $telegramWebhookService;
    protected array $data = [];

    public function __construct()
    {
        $this->notificationRepository = app(NotificationRepository::class);
        $this->telegramChatRepository = app(TelegramChatRepository::class);
        $this->clientRepository = app(ClientRepository::class);
        $this->telegramService = app(TelegramServiceInterface::class);
        $this->smsService = app(SmsServiceInterface::class);
        $this->telegramWebhookService = app(TelegramWebhookServiceInterface::class);
    }

    public function loadData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function validate(): self
    {
        $this->validateSpecificData();
        return $this;
    }

    abstract public function execute(): mixed;
    abstract protected function validateSpecificData(): void;
}
