<?php

namespace App\Domain\Order\VO;

enum SharpeningToolType: string
{
    case KitchenKnife = 'kitchen_knife';
    case ChefKnife = 'chef_knife';
    case HuntingKnife = 'hunting_knife';
    case Cleaver = 'cleaver';
    case Scissors = 'scissors';
    case HairdressingScissors = 'hairdressing_scissors';
    case GardenShears = 'garden_shears';
    case Axe = 'axe';
    case Chisel = 'chisel';
    case PlaneIron = 'plane_iron';
    case LawnmowerBlade = 'lawnmower_blade';
    case IceSkate = 'ice_skate';
    case ManicureTool = 'manicure_tool';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::KitchenKnife => 'Кухонный нож',
            self::ChefKnife => 'Шеф-нож',
            self::HuntingKnife => 'Охотничий нож',
            self::Cleaver => 'Топор-тяжёлка / секач',
            self::Scissors => 'Ножницы',
            self::HairdressingScissors => 'Парикмахерские ножницы',
            self::GardenShears => 'Садовые ножницы',
            self::Axe => 'Топор',
            self::Chisel => 'Стамеска / долото',
            self::PlaneIron => 'Нож рубанка',
            self::LawnmowerBlade => 'Нож газонокосилки',
            self::IceSkate => 'Коньки',
            self::ManicureTool => 'Маникюрный инструмент',
            self::Other => 'Другое',
        };
    }

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases(),
        );
    }

    public static function tryLabel(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value)?->label();
    }
}
