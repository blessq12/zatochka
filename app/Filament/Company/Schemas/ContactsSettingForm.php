<?php

namespace App\Filament\Company\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactsSettingForm
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
            TextInput::make('contact_person')
                ->label('Контактное лицо')
                ->required()
                ->maxLength(255),
            TextInput::make('phone')
                ->label('Телефон')
                ->required()
                ->maxLength(32),
            TextInput::make('phone_tel')
                ->label('Телефон для ссылки tel:')
                ->required()
                ->maxLength(32),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255),
            TextInput::make('address_main')
                ->label('Адрес')
                ->required()
                ->maxLength(500),
            Repeater::make('address_details')
                ->label('Подсказки к адресу')
                ->simple(
                    TextInput::make('text')
                        ->label('Строка')
                        ->required()
                        ->maxLength(500),
                )
                ->defaultItems(1)
                ->collapsible()
                ->collapsed(),
            TextInput::make('social_email')
                ->label('Email для соцблока')
                ->email()
                ->maxLength(255),
            Repeater::make('social_links')
                ->label('Соцсети')
                ->schema([
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('icon')
                        ->label('Иконка')
                        ->required()
                        ->maxLength(50),
                    TextInput::make('url')
                        ->label('Ссылка')
                        ->url()
                        ->required()
                        ->maxLength(500),
                ])
                ->defaultItems(1)
                ->collapsible()
                ->collapsed()
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                ->reorderable(),
        ];
    }
}
