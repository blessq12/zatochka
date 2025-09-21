<?php

namespace App;

enum UserRole: string
{
    case MANAGER = 'manager';
    case MASTER = 'master';

    /**
     * Получить все роли
     */
    public static function getAll(): array
    {
        return [
            self::MANAGER->value => 'Менеджер',
            self::MASTER->value => 'Мастер',
        ];
    }

    /**
     * Получить роли для выбора в формах
     */
    public static function getSelectable(): array
    {
        return self::getAll();
    }

    /**
     * Получить человекочитаемое название роли
     */
    public function getLabel(): string
    {
        return self::getAll()[$this->value] ?? $this->value;
    }

    /**
     * Проверить, может ли роль управлять пользователями
     */
    public function canManageUsers(): bool
    {
        return $this === self::MANAGER;
    }

    /**
     * Проверить, может ли роль работать с заказами
     */
    public function canWorkWithOrders(): bool
    {
        return in_array($this, [self::MANAGER, self::MASTER]);
    }

    /**
     * Проверить, может ли роль работать с финансами
     */
    public function canWorkWithFinances(): bool
    {
        return $this === self::MANAGER;
    }
}
