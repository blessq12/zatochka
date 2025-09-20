<?php

namespace App\Domain\Order\Enum;

enum OrderType: string
{
    case REPAIR = 'repair';
    case SHARPENING = 'sharpening';
    case DIAGNOSTIC = 'diagnostic';
    case REPLACEMENT = 'replacement';
    case MAINTENANCE = 'maintenance';
    case CONSULTATION = 'consultation';
    case WARRANTY = 'warranty';

    public function getLabel(): string
    {
        return match ($this) {
            self::REPAIR => 'Ремонт',
            self::SHARPENING => 'Заточка',
            self::DIAGNOSTIC => 'Диагностика',
            self::REPLACEMENT => 'Замена',
            self::MAINTENANCE => 'Обслуживание',
            self::CONSULTATION => 'Консультация',
            self::WARRANTY => 'Гарантийный ремонт',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::REPAIR => 'Ремонт оборудования',
            self::SHARPENING => 'Заточка инструментов',
            self::DIAGNOSTIC => 'Диагностика неисправности',
            self::REPLACEMENT => 'Замена деталей',
            self::MAINTENANCE => 'Плановое обслуживание',
            self::CONSULTATION => 'Консультация по ремонту',
            self::WARRANTY => 'Гарантийный ремонт',
        };
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }
}
