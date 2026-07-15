<?php

namespace App\Filament\CRM\Resources;

use App\Filament\CRM\Resources\ClientResource\Pages\CreateClient;
use App\Filament\CRM\Resources\ClientResource\Pages\EditClient;
use App\Filament\CRM\Resources\ClientResource\Pages\ListClients;
use App\Filament\CRM\Resources\ClientResource\Pages\ViewClient;
use App\Filament\Support\DomainResource;
use App\Infrastructure\CRM\Model\ClientModel;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ClientResource extends DomainResource
{
    protected static ?string $model = ClientModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Клиенты';

    protected static ?string $navigationLabel = 'Клиенты';

    protected static ?string $modelLabel = 'Клиент';

    protected static ?string $pluralModelLabel = 'Клиенты';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('ФИО')
                ->required()
                ->maxLength(255),
            TextInput::make('phone')
                ->label('Телефон')
                ->tel()
                ->telRegex('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/')
                ->mask('+7 (999) 999-99-99')
                ->placeholder('+7 (___) ___-__-__')
                ->required(),
            TextInput::make('email')
                ->label('Эл. почта')
                ->email()
                ->maxLength(255),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')
                ->label('ФИО')
                ->placeholder('—'),
            TextEntry::make('phone')
                ->label('Телефон'),
            TextEntry::make('email')
                ->label('Эл. почта')
                ->placeholder('—'),
            TextEntry::make('bonus_balance')
                ->label('Бонусы'),
            TextEntry::make('created_at')
                ->label('Создан')
                ->dateTime(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('ФИО')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Эл. почта')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('bonus_balance')
                    ->label('Бонусы')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()->label('Просмотр'),
                EditAction::make()->label('Редактировать'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'view' => ViewClient::route('/{record}'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
