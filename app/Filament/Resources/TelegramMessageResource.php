<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelegramMessageResource\Pages;
use App\Filament\Resources\TelegramMessageResource\RelationManagers;
use App\Models\TelegramMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TelegramMessageResource extends Resource
{
    protected static ?string $model = TelegramMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    
    protected static ?string $navigationLabel = 'Сообщения Telegram';
    
    protected static ?string $modelLabel = 'Сообщение';
    
    protected static ?string $pluralModelLabel = 'Сообщения';
    
    protected static ?string $navigationGroup = 'Telegram';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('chat_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('client_id')
                    ->numeric(),
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('direction')
                    ->required(),
                Forms\Components\DateTimePicker::make('sent_at')
                    ->required(),
                Forms\Components\Toggle::make('is_deleted')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('chat_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('direction'),
                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_deleted')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListTelegramMessages::route('/'),
            'create' => Pages\CreateTelegramMessage::route('/create'),
            'view' => Pages\ViewTelegramMessage::route('/{record}'),
            'edit' => Pages\EditTelegramMessage::route('/{record}/edit'),
        ];
    }
}
