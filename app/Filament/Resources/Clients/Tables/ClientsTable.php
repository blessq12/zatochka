<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn ($record): string => ClientResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('full_name')->label('Имя')->searchable(),
                TextColumn::make('phone')->label('Телефон')->searchable(),
                TextColumn::make('email')->label('Email')->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->dateTime('d.m.Y'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
