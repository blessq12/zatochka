<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Support\EquipmentFormData;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->recordUrl(fn ($record): string => EquipmentResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('brand')
                    ->label('Бренд')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('model')
                    ->label('Модель')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('serial_numbers_summary')
                    ->label('Серийные номера')
                    ->state(fn (EquipmentModel $record): string => EquipmentFormData::formatForListDisplay($record->serial_numbers))
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
