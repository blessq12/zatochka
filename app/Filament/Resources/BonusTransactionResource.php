<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BonusTransactionResource\Pages;
use App\Filament\Resources\BonusTransactionResource\RelationManagers;
use App\Models\BonusTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BonusTransactionResource extends Resource
{
    protected static ?string $model = BonusTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    protected static ?string $navigationLabel = 'Транзакции бонусов';
    
    protected static ?string $modelLabel = 'Транзакция';
    
    protected static ?string $pluralModelLabel = 'Транзакции';
    
    protected static ?string $navigationGroup = 'Бонусы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('client_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('order_id')
                    ->numeric(),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('idempotency_key')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('idempotency_key')
                    ->searchable(),
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
            'index' => Pages\ListBonusTransactions::route('/'),
            'create' => Pages\CreateBonusTransaction::route('/create'),
            'view' => Pages\ViewBonusTransaction::route('/{record}'),
            'edit' => Pages\EditBonusTransaction::route('/{record}/edit'),
        ];
    }
}
