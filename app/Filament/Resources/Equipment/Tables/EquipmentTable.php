<?php

namespace App\Filament\Resources\Equipment\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label('Название'),
                TextColumn::make('brand')->label('Бренд')->placeholder('—'),
                TextColumn::make('model')->label('Модель')->placeholder('—'),
                TextColumn::make('serial_numbers')
                    ->label('Серийные номера')
                    ->formatStateUsing(function (mixed $state): string {
                        if ($state === null || $state === [] || $state === '') {
                            return '—';
                        }

                        if (is_string($state)) {
                            $decoded = json_decode($state, true);

                            return is_array($decoded) && $decoded !== []
                                ? implode(', ', $decoded)
                                : $state;
                        }

                        return implode(', ', $state);
                    }),
            ]);
    }
}
