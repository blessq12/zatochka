<?php

namespace App\Filament\Company\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyInfoSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(self::components());
    }

    /** @return list<\Filament\Forms\Components\Component> */
    public static function components(): array
    {
        return [
            TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255),
            TextInput::make('owner_name')
                ->label('Владелец')
                ->required()
                ->maxLength(255),
            TextInput::make('inn')
                ->label('ИНН')
                ->maxLength(20),
            TextInput::make('ogrn')
                ->label('ОГРН')
                ->maxLength(20),
            Textarea::make('legal_address')
                ->label('Юридический адрес')
                ->rows(3)
                ->maxLength(1000),
            TextInput::make('actual_address')
                ->label('Фактический адрес')
                ->maxLength(500),
        ];
    }
}
