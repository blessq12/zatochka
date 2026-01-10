<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BonusSettingsResource\Pages;
use App\Filament\Resources\BonusSettingsResource\RelationManagers;
use App\Models\BonusSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BonusSettingsResource extends Resource
{
    protected static ?string $model = BonusSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Настройки бонусов';
    
    protected static ?string $modelLabel = 'Настройки';
    
    protected static ?string $pluralModelLabel = 'Настройки';
    
    protected static ?string $navigationGroup = 'Бонусы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('birthday_bonus')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('first_order_bonus')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('rate')
                    ->required()
                    ->numeric()
                    ->default(1.00),
                Forms\Components\TextInput::make('percent_per_order')
                    ->required()
                    ->numeric()
                    ->default(5.00),
                Forms\Components\TextInput::make('min_order_sum_for_spending')
                    ->required()
                    ->numeric()
                    ->default(1000.00),
                Forms\Components\TextInput::make('expire_days')
                    ->required()
                    ->numeric()
                    ->default(365),
                Forms\Components\TextInput::make('min_order_amount')
                    ->required()
                    ->numeric()
                    ->default(100.00),
                Forms\Components\TextInput::make('max_bonus_per_order')
                    ->required()
                    ->numeric()
                    ->default(1000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('birthday_bonus')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_order_bonus')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('percent_per_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_order_sum_for_spending')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expire_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_order_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_bonus_per_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListBonusSettings::route('/'),
            'create' => Pages\CreateBonusSettings::route('/create'),
            'view' => Pages\ViewBonusSettings::route('/{record}'),
            'edit' => Pages\EditBonusSettings::route('/{record}/edit'),
        ];
    }
}
