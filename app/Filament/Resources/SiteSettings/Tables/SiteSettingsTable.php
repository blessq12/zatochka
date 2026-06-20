<?php

namespace App\Filament\Resources\SiteSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingsTable
{
    private const LABELS = [
        'contacts' => 'Контакты',
        'schedule' => 'График работы',
        'delivery_info' => 'Доставка',
        'company' => 'Компания',
        'faq' => 'FAQ',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                TextColumn::make('key')
                    ->label('Ключ')
                    ->formatStateUsing(fn (string $state): string => self::LABELS[$state] ?? $state),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function labelForKey(string $key): string
    {
        return self::LABELS[$key] ?? $key;
    }
}
