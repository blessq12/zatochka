<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Клиенты';

    protected static ?string $modelLabel = 'Клиент';

    protected static ?string $pluralModelLabel = 'Клиенты';

    protected static ?string $navigationGroup = 'Клиенты';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->label('ФИО')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Иванов Иван Иванович'),
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->required()
                            ->maxLength(255)
                            ->unique('clients', 'phone', ignoreRecord: true)
                            ->placeholder('+7 (999) 123-45-67')
                            ->helperText('Формат: +7 (###) ###-##-##'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->nullable()
                            ->unique('clients', 'email', ignoreRecord: true)
                            ->placeholder('email@example.com')
                            ->helperText('Email должен быть уникальным'),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Дата рождения')
                            ->displayFormat('d.m.Y')
                            ->helperText('Дата рождения клиента'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Контактная информация')
                    ->schema([
                        Forms\Components\TextInput::make('telegram')
                            ->label('Telegram')
                            ->maxLength(255)
                            ->nullable()
                            ->unique('clients', 'telegram', ignoreRecord: true)
                            ->placeholder('@username')
                            ->helperText('Username в Telegram без символа @. Должен быть уникальным'),
                        Forms\Components\DateTimePicker::make('telegram_verified_at')
                            ->label('Telegram подтвержден')
                            ->displayFormat('d.m.Y H:i')
                            ->helperText('Дата подтверждения Telegram аккаунта')
                            ->visible(fn($record) => $record && filled($record->telegram_verified_at)),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Маркетинг')
                    ->schema([
                        Forms\Components\Select::make('marketing_source')
                            ->label('Источник клиента')
                            ->options(Order::getAvailableClientSources())
                            ->nullable()
                            ->helperText('Откуда клиент впервые узнал о нас'),

                        Forms\Components\Select::make('first_contact_channel')
                            ->label('Канал первого обращения')
                            ->options([
                                'telegram' => 'Telegram',
                                'whatsapp' => 'WhatsApp',
                                'instagram' => 'Instagram',
                                'phone' => 'Телефон',
                                'offline' => 'Оффлайн',
                                'other' => 'Другое',
                            ])
                            ->nullable(),

                        Forms\Components\Textarea::make('first_contact_notes')
                            ->label('Заметки по первому обращению')
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('marketing_notes')
                            ->label('Маркетинговые заметки о клиенте')
                            ->rows(2)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Доставка')
                    ->schema([
                        Forms\Components\TextInput::make('delivery_address')
                            ->label('Адрес доставки')
                            ->maxLength(255)
                            ->placeholder('Город, улица, дом, квартира')
                            ->helperText('Полный адрес для доставки заказов'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('ФИО')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('telegram')
                    ->label('Telegram')
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ? '@' . $state : '—')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('telegram_verified_at')
                    ->label('Telegram подтвержден')
                    ->boolean()
                    ->getStateUsing(fn($record) => filled($record->telegram_verified_at))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Дата рождения')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('bonus_balance')
                    ->label('Бонусные баллы')
                    ->getStateUsing(fn($record) => $record->getBonusAccount()->balance)
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('delivery_address')
                    ->label('Адрес доставки')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->delivery_address)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('marketing_source')
                    ->label('Источник клиента')
                    ->formatStateUsing(fn(?string $state): string => $state ? (Order::getAvailableClientSources()[$state] ?? $state) : '—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('first_contact_channel')
                    ->label('Канал первого контакта')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Удаленные')
                    ->placeholder('Все клиенты')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),
                Tables\Filters\TernaryFilter::make('telegram_verified_at')
                    ->label('Telegram подтвержден')
                    ->placeholder('Все клиенты')
                    ->trueLabel('Только подтвержденные')
                    ->falseLabel('Только неподтвержденные'),
                Tables\Filters\SelectFilter::make('marketing_source')
                    ->label('Источник клиента')
                    ->options(Order::getAvailableClientSources()),
                Tables\Filters\SelectFilter::make('first_contact_channel')
                    ->label('Канал первого контакта')
                    ->options([
                        'telegram' => 'Telegram',
                        'whatsapp' => 'WhatsApp',
                        'instagram' => 'Instagram',
                        'phone' => 'Телефон',
                        'offline' => 'Оффлайн',
                        'other' => 'Другое',
                    ]),
                Tables\Filters\Filter::make('has_orders_this_month')
                    ->label('Активные за текущий месяц')
                    ->query(function (Builder $query): Builder {
                        $monthStart = now()->startOfMonth();

                        return $query->whereHas('orders', function (Builder $ordersQuery) use ($monthStart) {
                            $ordersQuery
                                ->where('is_deleted', false)
                                ->where('created_at', '>=', $monthStart);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->tooltip('Просмотр'),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Редактировать'),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BonusTransactionsRelationManager::class,
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Основная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('ФИО')
                            ->icon('heroicon-o-user'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Телефон')
                            ->icon('heroicon-o-phone')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('birth_date')
                            ->label('Дата рождения')
                            ->date('d.m.Y')
                            ->icon('heroicon-o-calendar')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Контактная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('telegram')
                            ->label('Telegram')
                            ->formatStateUsing(fn($state) => $state ? '@' . $state : '—')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->placeholder('—'),
                        Infolists\Components\IconEntry::make('telegram_verified_at')
                            ->label('Telegram подтвержден')
                            ->boolean()
                            ->getStateUsing(fn($record) => filled($record->telegram_verified_at))
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('delivery_address')
                            ->label('Адрес доставки')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Маркетинг')
                    ->schema([
                        Infolists\Components\TextEntry::make('marketing_source')
                            ->label('Источник клиента')
                            ->formatStateUsing(fn(?string $state): string => $state ? (Order::getAvailableClientSources()[$state] ?? $state) : '—')
                            ->icon('heroicon-o-chart-bar-square')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('first_contact_channel')
                            ->label('Канал первого контакта')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('first_contact_notes')
                            ->label('Заметки по первому обращению')
                            ->columnSpanFull()
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('marketing_notes')
                            ->label('Маркетинговые заметки')
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Бонусный счет')
                    ->schema([
                        Infolists\Components\TextEntry::make('bonus_balance')
                            ->label('Баланс бонусов')
                            ->getStateUsing(fn($record) => $record->getBonusAccount()->balance)
                            ->numeric()
                            ->badge()
                            ->color('success')
                            ->size('lg')
                            ->icon('heroicon-o-gift'),
                        Infolists\Components\TextEntry::make('bonus_account.created_at')
                            ->label('Счет создан')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('bonus_account.updated_at')
                            ->label('Последнее обновление')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                    ])
                    ->columns(3),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
