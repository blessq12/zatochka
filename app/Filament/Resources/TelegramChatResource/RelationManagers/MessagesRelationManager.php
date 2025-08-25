<?php

namespace App\Filament\Resources\TelegramChatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $recordTitleAttribute = 'message_id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('message_id')
                    ->label('Message ID')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('direction')
                    ->label('Направление')
                    ->options([
                        'incoming' => 'Входящее',
                        'outgoing' => 'Исходящее',
                    ])
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Тип')
                    ->options([
                        'text' => 'Текст',
                        'photo' => 'Фото',
                        'document' => 'Документ',
                        'audio' => 'Аудио',
                        'video' => 'Видео',
                        'voice' => 'Голосовое',
                        'sticker' => 'Стикер',
                        'command' => 'Команда',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->label('Содержание')
                    ->rows(5),
                Forms\Components\DateTimePicker::make('sent_at')
                    ->label('Отправлено')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message_id')
            ->view('filament.resources.telegram-chat-resource.relation-managers.chat-view', [
                'messages' => $this->getRelationship()->get()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->label('Направление')
                    ->options([
                        'incoming' => 'Входящие',
                        'outgoing' => 'Исходящие',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'text' => 'Текст',
                        'photo' => 'Фото',
                        'document' => 'Документ',
                        'audio' => 'Аудио',
                        'video' => 'Видео',
                        'voice' => 'Голосовое',
                        'sticker' => 'Стикер',
                        'command' => 'Команда',
                    ]),
            ])
            ->headerActions([
                // Убираем создание для системных таблиц
            ])
            ->actions([
                // Убираем действия для чистого вида чата
            ])
            ->bulkActions([
                // Убираем bulk actions для системных таблиц
            ])
            ->defaultSort('sent_at', 'asc')
            ->paginated(false);
    }
}
