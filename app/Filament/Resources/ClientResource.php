<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                            ->visible(fn ($record) => $record && filled($record->telegram_verified_at)),
                    ])
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

                Forms\Components\Section::make('Безопасность')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->maxLength(255)
                            ->required()
                            ->helperText('Минимум 6 символов')
                            ->visible(fn ($operation) => $operation === 'create')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('password')
                            ->label('Новый пароль')
                            ->password()
                            ->maxLength(255)
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText('Оставьте пустым, чтобы не менять пароль. Минимум 6 символов.')
                            ->visible(fn ($operation) => $operation === 'edit')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удален')
                            ->default(false)
                            ->helperText('Пометить клиента как удаленного'),
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
                    ->formatStateUsing(fn ($state) => $state ? '@' . $state : '—')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('telegram_verified_at')
                    ->label('Telegram подтвержден')
                    ->boolean()
                    ->getStateUsing(fn ($record) => filled($record->telegram_verified_at))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Дата рождения')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('delivery_address')
                    ->label('Адрес доставки')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->delivery_address)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удален')
                    ->boolean()
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
