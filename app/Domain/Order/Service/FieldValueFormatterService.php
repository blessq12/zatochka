<?php

namespace App\Domain\Order\Service;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Enum\OrderType;
use App\Domain\Order\Enum\OrderUrgency;
use App\Domain\Order\FieldLabels;

class FieldValueFormatterService
{
    public static function formatValue(string $field, $value): string
    {
        if ($value === null) {
            return 'null';
        }

        return match ($field) {
            'status' => self::formatStatus($value),
            'type' => self::formatType($value),
            'urgency' => self::formatUrgency($value),
            'is_paid' => $value ? 'Да' : 'Нет',
            'is_deleted' => $value ? 'Да' : 'Нет',
            'paid_at', 'created_at', 'updated_at' => $value instanceof \DateTimeInterface
                ? $value->format('d.m.Y H:i')
                : $value,
            'total_amount', 'final_price', 'cost_price', 'profit' => is_numeric($value)
                ? number_format($value, 2) . ' ₽'
                : $value,
            default => (string) $value,
        };
    }

    public static function formatFieldLabel(string $field): string
    {
        return FieldLabels::getLabel($field);
    }

    private static function formatStatus($value): string
    {
        if ($value instanceof OrderStatus) {
            return $value->getLabel();
        }

        if (is_string($value)) {
            try {
                $status = OrderStatus::from($value);
                return $status->getLabel();
            } catch (\ValueError $e) {
                return $value;
            }
        }

        return (string) $value;
    }

    private static function formatType($value): string
    {
        if ($value instanceof OrderType) {
            return $value->getLabel();
        }

        if (is_string($value)) {
            try {
                $type = OrderType::from($value);
                return $type->getLabel();
            } catch (\ValueError $e) {
                return $value;
            }
        }

        return (string) $value;
    }

    private static function formatUrgency($value): string
    {
        if ($value instanceof OrderUrgency) {
            return $value->getLabel();
        }

        if (is_string($value)) {
            try {
                $urgency = OrderUrgency::from($value);
                return $urgency->getLabel();
            } catch (\ValueError $e) {
                return $value;
            }
        }

        return (string) $value;
    }
}
