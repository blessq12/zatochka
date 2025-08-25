<?php

namespace App\DTO;

abstract class BaseDTO
{
    /**
     * Создать DTO из массива
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    /**
     * Создать DTO из модели
     */
    public static function fromModel($model): static
    {
        return new static($model->toArray());
    }

    /**
     * Преобразовать DTO в массив
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Получить значение свойства
     */
    public function get(string $property): mixed
    {
        return $this->{$property} ?? null;
    }

    /**
     * Установить значение свойства
     */
    public function set(string $property, mixed $value): void
    {
        $this->{$property} = $value;
    }

    /**
     * Проверить наличие свойства
     */
    public function has(string $property): bool
    {
        return property_exists($this, $property);
    }
}
