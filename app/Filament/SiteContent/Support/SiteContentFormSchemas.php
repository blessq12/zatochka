<?php

namespace App\Filament\SiteContent\Support;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

final class SiteContentFormSchemas
{
    /** @return list<object> */
    public static function company(): array
    {
        return [
            Section::make()
                ->schema([
                    TextInput::make('company.owner_name')
                        ->label('Владелец')
                        ->required()
                        ->maxLength(255),
                    Grid::make(2)->schema([
                        TextInput::make('company.inn')
                            ->label('ИНН')
                            ->required()
                            ->maxLength(32),
                        TextInput::make('company.ogrn')
                            ->label('ОГРН')
                            ->required()
                            ->maxLength(32),
                    ]),
                    Textarea::make('company.legal_address')
                        ->label('Юридический адрес')
                        ->required()
                        ->rows(2),
                    Textarea::make('company.actual_address')
                        ->label('Фактический адрес')
                        ->required()
                        ->rows(2),
                ])
                ->columnSpanFull(),
        ];
    }

    /** @return list<object> */
    public static function contacts(): array
    {
        return [
            Section::make('Связь')
                ->icon(Heroicon::OutlinedPhone)
                ->schema([
                    TextInput::make('contacts.contact_person')
                        ->label('Контактное лицо')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Grid::make(2)->schema([
                        TextInput::make('contacts.phone')
                            ->label('Телефон')
                            ->tel()
                            ->telRegex('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/')
                            ->mask('+7 (999) 999-99-99')
                            ->placeholder('+7 (___) ___-__-__')
                            ->required(),
                        TextInput::make('contacts.email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),
                ])
                ->columnSpanFull(),
            Section::make('Адрес')
                ->icon(Heroicon::OutlinedMapPin)
                ->schema([
                    TextInput::make('contacts.address_main')
                        ->label('Адрес')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('contacts.entrance_directions')
                        ->label('Как найти вход')
                        ->rows(5)
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
            Section::make('Социальные сети')
                ->icon(Heroicon::OutlinedShare)
                ->schema([
                    Repeater::make('contacts.social_links')
                        ->label('Ссылки')
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('name')
                                    ->label('Название')
                                    ->required(),
                                TextInput::make('url')
                                    ->label('URL')
                                    ->required()
                                    ->url()
                                    ->columnSpan(2),
                            ]),
                        ])
                        ->addActionLabel('Добавить ссылку')
                        ->reorderable()
                        ->default([])
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];
    }

    /** @return list<object> */
    public static function delivery(): array
    {
        return [
            Section::make()
                ->schema([
                    Repeater::make('delivery.free_conditions')
                        ->label('Условия')
                        ->simple(
                            TextInput::make('value')
                                ->label('Условие')
                                ->required(),
                        )
                        ->addActionLabel('Добавить условие')
                        ->reorderable()
                        ->default([]),
                    Repeater::make('delivery.advantages')
                        ->label('Преимущества')
                        ->schema([
                            TextInput::make('title')
                                ->label('Заголовок')
                                ->required(),
                            Textarea::make('description')
                                ->label('Описание')
                                ->required()
                                ->rows(2),
                        ])
                        ->addActionLabel('Добавить преимущество')
                        ->reorderable()
                        ->default([]),
                ])
                ->columnSpanFull(),
        ];
    }

    /** @return list<object> */
    public static function schedule(): array
    {
        return [
            Section::make()
                ->schema([
                    Repeater::make('schedule.days')
                        ->label('Дни')
                        ->schema([
                            TextInput::make('id')->hidden(),
                            TextInput::make('name')->label('Название')->required(),
                            Toggle::make('is_day_off')->label('Выходной')->live(),
                            TextInput::make('day_off_text')
                                ->label('Текст выходного')
                                ->visible(fn (Get $get): bool => (bool) $get('is_day_off')),
                            TextInput::make('workshop')
                                ->label('Мастерская')
                                ->visible(fn (Get $get): bool => ! (bool) $get('is_day_off')),
                            TextInput::make('delivery')
                                ->label('Доставка')
                                ->visible(fn (Get $get): bool => ! (bool) $get('is_day_off')),
                        ])
                        ->collapsible()
                        ->reorderable()
                        ->default([]),
                ])
                ->columnSpanFull(),
        ];
    }

    /** @return list<object> */
    public static function faq(): array
    {
        return [
            Section::make()
                ->schema([
                    Repeater::make('faq.items')
                        ->label('Вопросы')
                        ->schema([
                            TextInput::make('id')->hidden(),
                            TextInput::make('question')->label('Вопрос')->required(),
                            Textarea::make('answer_lines')
                                ->label('Ответ')
                                ->required()
                                ->rows(4),
                        ])
                        ->collapsible()
                        ->reorderable()
                        ->default([]),
                ])
                ->columnSpanFull(),
        ];
    }
}
