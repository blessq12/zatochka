<?php

namespace App\Filament\SiteContent\Resources;

use App\Filament\SiteContent\Resources\SiteContactsResource\Pages\EditSiteContacts;
use App\Filament\SiteContent\Resources\SiteContactsResource\Pages\ListSiteContacts;
use App\Filament\Support\DomainResource;
use App\Infrastructure\SiteContent\Model\SiteContactsModel;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SiteContactsResource extends DomainResource
{
    protected static ?string $model = SiteContactsModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|UnitEnum|null $navigationGroup = 'Сайт';

    protected static ?string $navigationLabel = 'Контакты';

    protected static ?string $modelLabel = 'Контакты';

    protected static ?string $pluralModelLabel = 'Контакты';

    protected static ?int $navigationSort = 20;

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('contact_person')
                ->label('Контактное лицо')
                ->required()
                ->maxLength(255),
            TextInput::make('phone')
                ->label('Телефон')
                ->tel()
                ->telRegex('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/')
                ->mask('+7 (999) 999-99-99')
                ->placeholder('+7 (___) ___-__-__')
                ->required(),
            TextInput::make('phone_tel')
                ->label('Телефон для ссылки')
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
                ->maxLength(255),
            Repeater::make('address_details')
                ->label('Детали адреса')
                ->simple(
                    TextInput::make('detail')
                        ->label('Строка')
                        ->required(),
                )
                ->default([]),
            Repeater::make('social_links')
                ->label('Соцсети')
                ->schema([
                    TextInput::make('name')->label('Название')->required(),
                    TextInput::make('url')->label('URL')->required()->url(),
                    TextInput::make('icon')->label('Иконка')->nullable(),
                ])
                ->default([]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contact_person')->label('Контакт'),
                TextColumn::make('phone')->label('Телефон'),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteContacts::route('/'),
            'edit' => EditSiteContacts::route('/{record}/edit'),
        ];
    }
}
