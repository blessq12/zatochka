<?php

namespace App\Domain\Shared\Events;

use DateTimeImmutable;

abstract class DomainEvent
{
    public readonly DateTimeImmutable $occurredOn;
    public readonly string $eventId;

    public function __construct()
    {
        $this->occurredOn = new DateTimeImmutable();
        $this->eventId = uniqid('event_', true);
    }

    /**
     * Возвращает имя события для логирования и отладки
     */
    abstract public function eventName(): string;

    /**
     * Возвращает данные события для логирования
     */
    abstract public function eventData(): array;

    /**
     * Возвращает версию события для совместимости
     */
    public function eventVersion(): string
    {
        return '1.0';
    }

    /**
     * Возвращает метаданные события
     */
    public function eventMetadata(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_name' => $this->eventName(),
            'event_version' => $this->eventVersion(),
            'occurred_on' => $this->occurredOn->format('c'),
            'aggregate_type' => $this->getAggregateType(),
        ];
    }

    /**
     * Возвращает тип агрегата, к которому относится событие
     */
    protected function getAggregateType(): string
    {
        $className = static::class;
        $parts = explode('\\', $className);
        $eventName = end($parts);

        // Убираем суффикс "Event" и добавляем "Aggregate"
        $aggregateName = str_replace('Event', '', $eventName);

        return $aggregateName . 'Aggregate';
    }
}
