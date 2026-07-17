<?php

namespace App\Filament\Support;

use App\Infrastructure\CRM\Model\ClientModel;
use Filament\Forms\Components\Select;

/**
 * Общий селект клиента для Filament-форм (Order, Equipment).
 * Обязательность/placeholder настраиваются на месте вызова.
 */
final class ClientSelectField
{
    public static function make(string $name = 'client_id'): Select
    {
        return Select::make($name)
            ->label('Клиент')
            ->options(fn (): array => self::options())
            ->getOptionLabelUsing(function ($value): ?string {
                if (blank($value)) {
                    return null;
                }

                $client = ClientModel::query()->find((int) $value);

                return $client === null ? null : self::label($client);
            })
            ->searchable()
            ->helperText('Выберите существующего или зарегистрируйте нового кнопкой справа');
    }

    /** @return array<int, string> */
    public static function options(): array
    {
        return ClientModel::query()
            ->orderBy('name')
            ->orderBy('phone')
            ->get()
            ->mapWithKeys(static fn (ClientModel $client): array => [(int) $client->id => self::label($client)])
            ->all();
    }

    public static function label(ClientModel $client): string
    {
        return trim(($client->name ?: 'Без имени').' · '.$client->phone);
    }
}
