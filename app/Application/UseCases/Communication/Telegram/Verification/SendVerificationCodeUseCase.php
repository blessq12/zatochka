<?php

namespace App\Application\UseCases\Communication\Telegram\Verification;

use App\Application\UseCases\Communication\BaseCommunicationUseCase;
use App\Domain\Communication\Entity\TelegramChat;
use Illuminate\Support\Facades\Log;

class SendVerificationCodeUseCase extends BaseCommunicationUseCase
{
    /**
     * $authContext is auth via sanctum client
     */
    public function validateSpecificData(): void
    {
        if (!$this->authContext) {
            throw new \Exception('Client not authenticated');
        }

        if (!$this->authContext->telegram || empty(trim($this->authContext->telegram))) {
            throw new \Exception('Укажите Telegram username в настройках профиля');
        }
    }

    public function execute(): mixed
    {
        $telegramUsername = trim($this->authContext->telegram);
        $telegramChat = $this->findTelegramChatByUsername($telegramUsername);

        if (!$telegramChat) {
            throw new \Exception('Telegram чат не найден. Перейдите в бота @zatochka_bot и нажмите /start');
        }

        $verificationCode = $this->generateVerificationCode();

        // Сохраняем код в кэш на 5 минут
        $this->storeVerificationCode($verificationCode, $telegramUsername, 5);

        // Отправляем сообщение в Telegram
        $message = $this->formatVerificationMessage($verificationCode);
        $result = $this->telegramMessageService->sendMessage(
            $telegramChat->getChatId(),
            $message
        );

        if (!$result['success']) {
            Log::error('Failed to send Telegram verification code', [
                'telegram_username' => $telegramUsername,
                'chat_id' => $telegramChat->getChatId(),
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            throw new \Exception('Не удалось отправить код подтверждения. Попробуйте позже.');
        }

        Log::info('Telegram verification code sent successfully', [
            'telegram_username' => $telegramUsername,
            'chat_id' => $telegramChat->getChatId(),
            'message_id' => $result['message_id'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => 'Код подтверждения отправлен в Telegram',
            'telegram_username' => $telegramUsername,
            'expires_in_minutes' => 5,
        ];
    }

    /**
     * Ищет Telegram чат по username
     */
    protected function findTelegramChatByUsername(string $username): ?TelegramChat
    {
        // Убираем @ если есть
        $cleanUsername = ltrim($username, '@');

        // Ищем чат по username через репозиторий
        return $this->telegramChatRepository->findByUsername($cleanUsername);
    }

    /**
     * Форматирует сообщение с кодом подтверждения
     */
    private function formatVerificationMessage(string $code): string
    {
        return "🔐 <b>Код подтверждения</b>\n\n" .
            "Ваш код для подтверждения: <code>{$code}</code>\n\n" .
            "⚠️ Код действителен 5 минут\n" .
            "❌ Не передавайте код третьим лицам";
    }
}
