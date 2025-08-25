<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TelegramChatRelationManager extends RelationManager
{
    protected static string $relationship = 'telegramChat';

    protected static ?string $recordTitleAttribute = 'username';

    public function form(Form $form): Form
    {
        return $form
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->formatStateUsing(fn(string $state): string => '@' . $state)
                    ->searchable()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('last_activity_at')
                    ->label('Последняя активность')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активность'),
            ])
            ->headerActions([
                // Убираем создание для системных таблиц
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Убираем редактирование для системных таблиц
            ])
            ->bulkActions([
                // Убираем bulk actions для системных таблиц
            ]);
    }
}
