<?php

namespace App\Filament\CRM\Resources\ClientResource\Support;

use App\Domain\Order\VO\OrderNumber;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Order\Model\OrderModel;

final class ClientPresentation
{
    public static function displayName(ClientModel $record): string
    {
        return filled($record->name)
            ? (string) $record->name
            : 'Без имени';
    }

    public static function displayPhone(ClientModel $record): string
    {
        return filled($record->phone)
            ? (string) $record->phone
            : '—';
    }

    public static function orderNumberLabel(OrderModel $order): string
    {
        $number = $order->number;

        if (! filled($number)) {
            return (string) $order->id;
        }

        try {
            return (string) new OrderNumber((string) $number);
        } catch (\Throwable) {
            return (string) $number;
        }
    }

    public static function equipmentLabel(ClientEquipmentModel $equipment): string
    {
        if (filled($equipment->title)) {
            return (string) $equipment->title;
        }

        $brandModel = trim(($equipment->brand ?? '').' '.($equipment->model_name ?? ''));

        return $brandModel !== '' ? $brandModel : 'Оборудование #'.$equipment->id;
    }

    public static function equipmentDetails(ClientEquipmentModel $equipment): string
    {
        $parts = array_filter([
            filled($equipment->brand) ? (string) $equipment->brand : null,
            filled($equipment->model_name) ? (string) $equipment->model_name : null,
        ]);

        return $parts === [] ? '—' : implode(' · ', $parts);
    }

    public static function equipmentComponentsSummary(ClientEquipmentModel $equipment): string
    {
        $names = $equipment->components
            ->pluck('name')
            ->filter(static fn ($name): bool => filled($name))
            ->map(static fn ($name): string => (string) $name)
            ->values()
            ->all();

        if ($names === []) {
            return '—';
        }

        return implode(', ', $names);
    }
}
