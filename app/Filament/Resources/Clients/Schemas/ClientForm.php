<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema, bool $isCreate = false): Schema
    {
        return $schema
            ->components([
                TextInput::make('full_name')
                    ->label('ФИО')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->required($isCreate)
                    ->disabled(! $isCreate)
                    ->dehydrated($isCreate)
                    ->maxLength(32),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                DatePicker::make('birth_date')
                    ->label('Дата рождения')
                    ->native(false)
                    ->displayFormat('d.m.Y'),
                Textarea::make('delivery_address')
                    ->label('Адрес доставки')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }
}
