<?php

namespace App\Filament\Resources\Masters\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MastersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Имя')
                    ->formatStateUsing(fn ($record): string => trim($record->name.' '.($record->surname ?? ''))),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
