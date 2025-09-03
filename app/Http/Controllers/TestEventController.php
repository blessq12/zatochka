<?php

namespace App\Http\Controllers;

use App\Domain\Shared\Events\EventBusInterface;
use App\Domain\Users\Events\UserRegistered;
use App\Domain\Users\ValueObjects\Email;
use App\Domain\Users\ValueObjects\UserId;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class TestEventController extends Controller
{
    public function __construct(
        private readonly EventBusInterface $eventBus
    ) {}

    /**
     * Тестируем публикацию события UserRegistered
     */
    public function testUserRegisteredEvent(): JsonResponse
    {
        try {
            // Создаём тестовое событие
            $event = new UserRegistered(
                0, // Временный ID для тестирования
                'Тестовый Пользователь',
                Email::fromString('test@example.com')
            );

            // Публикуем событие
            $this->eventBus->publish($event);

            return response()->json([
                'success' => true,
                'message' => 'Event published successfully',
                'event' => [
                    'name' => $event->eventName(),
                    'id' => $event->eventId,
                    'occurred_on' => $event->occurredOn->format('c'),
                    'data' => $event->eventData(),
                    'metadata' => $event->eventMetadata()
                ],
                'subscribers_count' => $this->eventBus->getSubscribers(UserRegistered::class)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Тестируем подписку на события
     */
    public function testEventSubscription(): JsonResponse
    {
        try {
            // Создаём анонимный обработчик
            $handler = function (UserRegistered $event) {
                Log::info('Anonymous handler executed', [
                    'event_id' => $event->eventId,
                    'user_name' => $event->name
                ]);
            };

            // Подписываемся на событие
            $this->eventBus->subscribe(UserRegistered::class, $handler);

            // Проверяем, что подписчик добавлен
            $hasSubscribers = $this->eventBus->hasSubscribers(UserRegistered::class);
            $subscribers = $this->eventBus->getSubscribers(UserRegistered::class);

            return response()->json([
                'success' => true,
                'message' => 'Event subscription test completed',
                'has_subscribers' => $hasSubscribers,
                'subscribers_count' => count($subscribers)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Получаем статистику EventBus
     */
    public function getEventBusStats(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Event bus stats endpoint',
                'subscribers_count' => count($this->eventBus->getSubscribers(UserRegistered::class))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllEvents()
    {
        $data = [];
        // Убираем неопределенную переменную $user
        return response()->json($data);
    }
}
