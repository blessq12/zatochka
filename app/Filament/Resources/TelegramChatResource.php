<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelegramChatResource\Pages;
use App\Filament\Resources\TelegramChatResource\RelationManagers;
use App\Models\TelegramChat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TelegramChatResource extends Resource
{
    protected static ?string $model = TelegramChat::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Telegram чаты';
    protected static ?int $navigationSort = 6;
    protected static ?string $breadcrumb = 'Telegram чаты';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('chat_id')
                            ->label('Chat ID')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('first_name')
                            ->label('Имя')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Фамилия')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                        Forms\Components\DateTimePicker::make('last_activity_at')
                            ->label('Последняя активность'),
                    ])->columns(2),

                Forms\Components\Section::make('Связанный клиент')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->relationship('client', 'full_name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Выберите клиента'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => '@' . $state),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Полное имя')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('chat_id')
                    ->label('Chat ID')
                    ->sortable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('last_activity_at')
                    ->label('Последняя активность')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Сообщений')
                    ->counts('messages')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активность'),
                Tables\Filters\Filter::make('has_client')
                    ->label('С привязанным клиентом')
                    ->query(fn(Builder $query): Builder => $query->whereHas('client')),
                Tables\Filters\Filter::make('no_client')
                    ->label('Без привязанного клиента')
                    ->query(fn(Builder $query): Builder => $query->whereDoesntHave('client')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('activate')
                    ->label('Активировать')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(TelegramChat $record): bool => !$record->is_active)
                    ->action(fn(TelegramChat $record) => $record->activate()),
                Tables\Actions\Action::make('deactivate')
                    ->label('Деактивировать')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(TelegramChat $record): bool => $record->is_active)
                    ->action(fn(TelegramChat $record) => $record->deactivate()),
            ])
            ->bulkActions([
                // Убираем bulk actions для системных таблиц
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTelegramChats::route('/'),
            'view' => Pages\ViewTelegramChat::route('/{record}'),
        ];
    }
}
