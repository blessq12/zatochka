<?php

namespace App\Infrastructure\Events;

use App\Domain\Shared\Events\EventBusInterface;
use Illuminate\Support\Facades\Log;

class EventBus implements EventBusInterface
{
    private array $subscribers = [];

    public function publish(object $event): void
    {
        try {
            $eventClass = get_class($event);

            if (isset($this->subscribers[$eventClass])) {
                foreach ($this->subscribers[$eventClass] as $handler) {
                    try {
                        $handler($event);
                    } catch (\Exception $e) {
                        Log::error('Event handler failed', [
                            'event' => $eventClass,
                            'handler' => is_array($handler) ? get_class($handler[0]) . '::' . $handler[1] : 'closure',
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to publish event', [
                'event' => get_class($event),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function subscribe(string $eventClass, callable $handler): void
    {
        if (!isset($this->subscribers[$eventClass])) {
            $this->subscribers[$eventClass] = [];
        }

        $this->subscribers[$eventClass][] = $handler;
    }

    public function unsubscribe(string $eventClass, callable $handler): void
    {
        if (isset($this->subscribers[$eventClass])) {
            $this->subscribers[$eventClass] = array_filter(
                $this->subscribers[$eventClass],
                fn($subscriber) => $subscriber !== $handler
            );

            Log::debug('Event handler unsubscribed', [
                'event' => $eventClass,
                'handler' => is_array($handler) ? get_class($handler[0]) . '::' . $handler[1] : 'closure',
                'remaining_subscribers' => count($this->subscribers[$eventClass])
            ]);
        }
    }

    public function hasSubscribers(string $eventClass): bool
    {
        return isset($this->subscribers[$eventClass]) && !empty($this->subscribers[$eventClass]);
    }

    public function getSubscribers(string $eventClass): array
    {
        return $this->subscribers[$eventClass] ?? [];
    }

    /**
     * Возвращает статистику по событиям
     */
    public function getEventStats(): array
    {
        $stats = [];
        foreach ($this->subscribers as $eventClass => $handlers) {
            $stats[$eventClass] = count($handlers);
        }
        return $stats;
    }

    /**
     * Очищает всех подписчиков (для тестирования)
     */
    public function clearSubscribers(): void
    {
        $this->subscribers = [];
    }
}
