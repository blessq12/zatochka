<?php

namespace App\Filament\Resources\Managers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ManagerForm
{
    public static function configure(Schema $schema, bool $isCreate): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255),
                TextInput::make('surname')
                    ->label('Фамилия')
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->maxLength(32),
                TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->revealable()
                    ->required($isCreate)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->maxLength(255),
            ]);
    }
}
