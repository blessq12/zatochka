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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TelegramChatResource extends Resource
{
    protected static ?string $model = TelegramChat::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static ?string $navigationLabel = 'Telegram чаты';
    
    protected static ?string $modelLabel = 'Telegram чат';
    
    protected static ?string $pluralModelLabel = 'Telegram чаты';
    
    protected static ?string $navigationGroup = 'Telegram';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('client_id')
                    ->numeric(),
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('chat_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\TextInput::make('metadata'),
                Forms\Components\Toggle::make('is_deleted')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('chat_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => Pages\ListTelegramChats::route('/'),
            'create' => Pages\CreateTelegramChat::route('/create'),
            'view' => Pages\ViewTelegramChat::route('/{record}'),
            'edit' => Pages\EditTelegramChat::route('/{record}/edit'),
        ];
    }
}
