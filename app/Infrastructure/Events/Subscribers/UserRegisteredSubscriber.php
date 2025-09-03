<?php

namespace App\Infrastructure\Events\Subscribers;

use App\Domain\Users\Events\UserRegistered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserRegisteredSubscriber
{
    public function handle(UserRegistered $event): void
    {
        // Логируем событие
        Log::info('User registered', [
            'user_id' => (string) $event->userId,
            'name' => $event->name,
            'email' => (string) $event->email,
            'timestamp' => now()->toISOString()
        ]);

        // Отправляем приветственное письмо (заглушка)
        $this->sendWelcomeEmail($event);
        
        // Можно добавить другие действия:
        // - Создание профиля пользователя
        // - Отправка уведомления администратору
        // - Синхронизация с внешними системами
        // - Создание бонусного счёта
    }

    private function sendWelcomeEmail(UserRegistered $event): void
    {
        try {
            // Здесь будет реальная отправка email
            // Mail::to((string) $event->email)->send(new WelcomeEmail($event->name));
            
            Log::info('Welcome email queued for user', [
                'user_id' => (string) $event->userId,
                'email' => (string) $event->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => (string) $event->userId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
