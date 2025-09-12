<?php

namespace App\Filament\Resources\Manager\ClientResource\Pages;

use App\Filament\Resources\Manager\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Основная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('ФИО'),

                        Infolists\Components\TextEntry::make('phone')
                            ->label('Телефон')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('telegram')
                            ->label('Telegram')
                            ->formatStateUsing(fn($state) => $state ? '@' . $state : 'Не указан')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('birth_date')
                            ->label('Дата рождения')
                            ->date('d.m.Y')
                            ->placeholder('Не указана'),

                        Infolists\Components\TextEntry::make('delivery_address')
                            ->label('Адрес доставки')
                            ->placeholder('Не указан')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Системная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('is_deleted')
                            ->label('Статус')
                            ->formatStateUsing(fn($state) => $state ? 'Удален' : 'Активен')
                            ->badge()
                            ->color(fn($state) => $state ? 'danger' : 'success'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Дата создания')
                            ->dateTime('d.m.Y H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Дата обновления')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(3),
            ]);
    }
}
