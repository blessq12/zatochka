<?php

namespace App\Domain\Company\Enum;

enum UserRole: string
{
    case MANAGER = 'manager';
    case MASTER = 'master';

    public function getLabel(): string
    {
        return match ($this) {
            self::MANAGER => 'Менеджер',
            self::MASTER => 'Мастер',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MANAGER => 'success',
            self::MASTER => 'warning',
        };
    }

    public static function getOptions(): array
    {
        return [
            self::MANAGER->value => self::MANAGER->getLabel(),
            self::MASTER->value => self::MASTER->getLabel(),
        ];
    }

    public static function getAll(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromArray(array $roles): array
    {
        return array_filter(
            array_map(fn($role) => self::tryFrom($role), $roles),
            fn($role) => $role !== null
        );
    }

    public static function toArray(array $roleEnums): array
    {
        return array_map(fn(UserRole $role) => $role->value, $roleEnums);
    }

    public static function validate(array $roles): bool
    {
        if (empty($roles)) {
            return false;
        }

        foreach ($roles as $role) {
            if (!self::tryFrom($role)) {
                return false;
            }
        }

        return true;
    }
}
