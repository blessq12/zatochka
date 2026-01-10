<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountRuleResource\Pages;
use App\Filament\Resources\DiscountRuleResource\RelationManagers;
use App\Models\DiscountRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DiscountRuleResource extends Resource
{
    protected static ?string $model = DiscountRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    
    protected static ?string $navigationLabel = 'Правила скидок';
    
    protected static ?string $modelLabel = 'Правило скидки';
    
    protected static ?string $pluralModelLabel = 'Правила скидок';
    
    protected static ?string $navigationGroup = 'Скидки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('conditions'),
                Forms\Components\DateTimePicker::make('active_from'),
                Forms\Components\DateTimePicker::make('active_to'),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Toggle::make('is_deleted')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('active_from')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('active_to')
                    ->dateTime()
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
            'index' => Pages\ListDiscountRules::route('/'),
            'create' => Pages\CreateDiscountRule::route('/create'),
            'view' => Pages\ViewDiscountRule::route('/{record}'),
            'edit' => Pages\EditDiscountRule::route('/{record}/edit'),
        ];
    }
}
