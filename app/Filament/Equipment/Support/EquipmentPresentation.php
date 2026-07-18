<?php

namespace App\Filament\Equipment\Support;

use App\Infrastructure\Equipment\Model\ClientEquipmentModel;

final class EquipmentPresentation
{
    public static function clientListingName(ClientEquipmentModel $record): string
    {
        if ($record->client === null) {
            return 'Без клиента';
        }

        return filled($record->client->name)
            ? (string) $record->client->name
            : 'Без имени';
    }

    public static function clientListingPhone(ClientEquipmentModel $record): string
    {
        if ($record->client === null) {
            return '—';
        }

        return filled($record->client->phone)
            ? (string) $record->client->phone
            : '—';
    }
}
