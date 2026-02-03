<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterResource\Pages;
use App\Models\Master;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class MasterResource extends Resource
{
    protected static ?string $model = Master::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Мастера';

    protected static ?string $modelLabel = 'Мастер';

    protected static ?string $pluralModelLabel = 'Мастера';

    protected static ?string $navigationGroup = 'Организация';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Имя мастера'),
                        Forms\Components\TextInput::make('surname')
                            ->label('Фамилия')
                            ->maxLength(255)
                            ->placeholder('Фамилия мастера'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, table: 'masters')
                            ->placeholder('master@example.com'),
                        Forms\Components\TextInput::make('phone')
                            ->label('Номер телефона')
                            ->maxLength(255)
                            ->placeholder('+7 (999) 123-45-67')
                            ->mask("+7 (***) ***-**-**")
                            ->helperText('Формат: +7 (###) ###-##-##'),
                        Forms\Components\TextInput::make('telegram_username')
                            ->label('Telegram')
                            ->maxLength(255)
                            ->placeholder('username')
                            ->helperText('Username в Telegram без символа @'),
                        Forms\Components\Toggle::make('notifications_enabled')
                            ->label('Получать уведомления')
                            ->default(true)
                            ->helperText('Включить уведомления для мастера'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Безопасность')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->maxLength(255)
                            ->required()
                            ->helperText('Минимум 6 символов')
                            ->visible(fn($operation) => $operation === 'create')
                            ->columnSpanFull()
                            ->dehydrated()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state)),
                        Forms\Components\TextInput::make('password')
                            ->label('Новый пароль')
                            ->password()
                            ->maxLength(255)
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->helperText('Оставьте пустым, чтобы не менять пароль. Минимум 6 символов.')
                            ->visible(fn($operation) => $operation === 'edit')
                            ->columnSpanFull()
                            ->rules(['min:6']),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удален')
                            ->default(false)
                            ->helperText('Пометить мастера как удаленного'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('surname')
                    ->label('Фамилия')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('telegram_username')
                    ->label('Telegram')
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ? '@' . ltrim($state, '@') : '—')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('notifications_enabled')
                    ->label('Уведомления')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Заказов')
                    ->counts('orders')
                    ->sortable(),
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
                    ->placeholder('Все мастера')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->tooltip('Просмотр'),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Редактировать'),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListMasters::route('/'),
            'create' => Pages\CreateMaster::route('/create'),
            'view' => Pages\ViewMaster::route('/{record}'),
            'edit' => Pages\EditMaster::route('/{record}/edit'),
        ];
    }
}
