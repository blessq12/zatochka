<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Filament\Resources\StockMovementResource\RelationManagers;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    
    protected static ?string $navigationLabel = 'Движения товаров';
    
    protected static ?string $modelLabel = 'Движение';
    
    protected static ?string $pluralModelLabel = 'Движения';
    
    protected static ?string $navigationGroup = 'Склад';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('stock_item_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('part_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('part_sku')
                    ->maxLength(255),
                Forms\Components\TextInput::make('part_purchase_price')
                    ->numeric(),
                Forms\Components\TextInput::make('part_retail_price')
                    ->numeric(),
                Forms\Components\TextInput::make('part_unit')
                    ->maxLength(20),
                Forms\Components\TextInput::make('part_supplier')
                    ->maxLength(255),
                Forms\Components\TextInput::make('part_manufacturer')
                    ->maxLength(255),
                Forms\Components\TextInput::make('part_model')
                    ->maxLength(255),
                Forms\Components\TextInput::make('movement_type')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('order_id')
                    ->numeric(),
                Forms\Components\TextInput::make('repair_id')
                    ->numeric(),
                Forms\Components\TextInput::make('supplier')
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_price')
                    ->numeric(),
                Forms\Components\TextInput::make('total_amount')
                    ->numeric(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('reference_number')
                    ->maxLength(100),
                Forms\Components\DateTimePicker::make('movement_date')
                    ->required(),
                Forms\Components\TextInput::make('created_by')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stock_item_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('part_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('part_sku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('part_purchase_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('part_retail_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('part_unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('part_supplier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('part_manufacturer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('part_model')
                    ->searchable(),
                Tables\Columns\TextColumn::make('movement_type'),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('repair_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('movement_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_by')
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
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
            'view' => Pages\ViewStockMovement::route('/{record}'),
            'edit' => Pages\EditStockMovement::route('/{record}/edit'),
        ];
    }
}
