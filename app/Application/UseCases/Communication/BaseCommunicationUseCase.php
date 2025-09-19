<?php

namespace App\Application\UseCases\Communication;

use App\Application\UseCases\UseCaseInterface;
use App\Domain\Communication\Service\TelegramMessageServiceInterface;
use App\Domain\Communication\Repository\TelegramChatRepository;
use App\Domain\Client\Repository\ClientRepository;

abstract class BaseCommunicationUseCase implements UseCaseInterface
{
    protected $authContext;
    protected array $data = [];
    protected TelegramMessageServiceInterface $telegramMessageService;
    protected TelegramChatRepository $telegramChatRepository;
    protected ClientRepository $clientRepository;

    public function __construct()
    {
        $this->authContext = auth('sanctum')->user();
        $this->telegramMessageService = app(TelegramMessageServiceInterface::class);
        $this->telegramChatRepository = app(TelegramChatRepository::class);
        $this->clientRepository = app(ClientRepository::class);
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

    /**
     * Генерирует 6-значный код подтверждения
     */
    protected function generateVerificationCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Сохраняет код подтверждения в кэш
     */
    protected function storeVerificationCode(string $code, string $telegramUsername, int $ttlMinutes = 5): void
    {
        $key = "telegram_verification_code_{$telegramUsername}";
        cache()->put($key, $code, now()->addMinutes($ttlMinutes));
    }

    /**
     * Получает код подтверждения из кэша
     */
    protected function getVerificationCode(string $telegramUsername): ?string
    {
        $key = "telegram_verification_code_{$telegramUsername}";
        return cache()->get($key);
    }

    /**
     * Удаляет код подтверждения из кэша
     */
    protected function clearVerificationCode(string $telegramUsername): void
    {
        $key = "telegram_verification_code_{$telegramUsername}";
        cache()->forget($key);
    }
}
