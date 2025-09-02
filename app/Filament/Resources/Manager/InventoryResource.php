<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\InventoryResource\Pages;
use App\Models\InventoryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryResource extends Resource
{
    protected static ?string $model = InventoryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Склад';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01),

                        Forms\Components\TextInput::make('unit')
                            ->label('Единица измерения')
                            ->required()
                            ->maxLength(20)
                            ->default('шт'),

                        Forms\Components\TextInput::make('min_stock')
                            ->label('Минимальный запас')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удален')
                            ->default(false),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->sortable()
                    ->color(
                        fn(string $state, $record): string =>
                        $record->quantity <= $record->min_stock ? 'danger' : 'success'
                    ),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Ед. изм.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Мин. запас')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('stock_status')
                    ->label('Статус')
                    ->formatStateUsing(function ($record): string {
                        if ($record->quantity <= 0) return 'Нет в наличии';
                        if ($record->quantity <= $record->min_stock) return 'Критический запас';
                        return 'В наличии';
                    })
                    ->colors([
                        'danger' => 'Нет в наличии',
                        'warning' => 'Критический запас',
                        'success' => 'В наличии',
                    ]),

                Tables\Columns\TextColumn::make('inventoryTransactions_count')
                    ->label('Операций')
                    ->counts('inventoryTransactions')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Только активные')
                    ->query(fn(Builder $query): Builder => $query->where('is_deleted', false))
                    ->default(),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Нет в наличии')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', '<=', 0)),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Критический запас')
                    ->query(fn(Builder $query): Builder => $query->whereRaw('quantity <= min_stock AND quantity > 0')),

                Tables\Filters\Filter::make('in_stock')
                    ->label('В наличии')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', '>', 0)->whereRaw('quantity > min_stock')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('transactions')
                    ->label('Операции')
                    ->icon('heroicon-o-arrow-path')
                    ->url(fn(InventoryItem $record): string => route('filament.admin.resources.manager.inventory-transactions.index', ['tableFilters[item_id][value]' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListInventory::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'view' => Pages\ViewInventory::route('/{record}'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('inventoryTransactions');
    }
}
