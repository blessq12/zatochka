<?php

namespace App\Domain\Shared\Events;

interface EventBusInterface
{
    /**
     * Публикует событие в систему
     */
    public function publish(object $event): void;

    /**
     * Подписывает обработчик на определённый тип события
     */
    public function subscribe(string $eventClass, callable $handler): void;

    /**
     * Отписывает обработчик от определённого типа события
     */
    public function unsubscribe(string $eventClass, callable $handler): void;

    /**
     * Проверяет, есть ли подписчики на определённый тип события
     */
    public function hasSubscribers(string $eventClass): bool;

    /**
     * Возвращает список подписчиков на определённый тип события
     */
    public function getSubscribers(string $eventClass): array;
}
