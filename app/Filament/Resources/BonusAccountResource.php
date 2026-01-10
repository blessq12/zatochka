<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BonusAccountResource\Pages;
use App\Filament\Resources\BonusAccountResource\RelationManagers;
use App\Models\BonusAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BonusAccountResource extends Resource
{
    protected static ?string $model = BonusAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    
    protected static ?string $navigationLabel = 'Бонусные счета';
    
    protected static ?string $modelLabel = 'Бонусный счет';
    
    protected static ?string $pluralModelLabel = 'Бонусные счета';
    
    protected static ?string $navigationGroup = 'Бонусы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('client_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListBonusAccounts::route('/'),
            'create' => Pages\CreateBonusAccount::route('/create'),
            'view' => Pages\ViewBonusAccount::route('/{record}'),
            'edit' => Pages\EditBonusAccount::route('/{record}/edit'),
        ];
    }
}
