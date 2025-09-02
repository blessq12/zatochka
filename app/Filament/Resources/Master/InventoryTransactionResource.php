<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\InventoryTransactionResource\Pages;
use App\Models\InventoryTransaction;
use App\Models\InventoryItem;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryTransactionResource extends Resource
{
    protected static ?string $model = InventoryTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Склад';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о заказе')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->options(Order::where('master_id', fn() => auth()->id())->pluck('order_number', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('order.client.full_name')
                            ->label('Клиент')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('order.serviceType.name')
                            ->label('Тип услуги')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),

                Forms\Components\Section::make('Операция со складом')
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->label('Товар/Материал')
                            ->options(InventoryItem::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('item.sku')
                            ->label('SKU')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('item.quantity')
                            ->label('Остаток на складе')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('type')
                            ->label('Тип операции')
                            ->options([
                                'out' => 'Списание',
                                'adjustment' => 'Корректировка',
                            ])
                            ->default('out')
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->required(),

                        Forms\Components\TextInput::make('item.unit')
                            ->label('Единица измерения')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Forms\Components\Section::make('Описание')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Описание операции')
                            ->rows(3)
                            ->required()
                            ->maxLength(500),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('item.name')
                    ->label('Товар/Материал')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('item.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Тип')
                    ->colors([
                        'danger' => 'out',
                        'warning' => 'adjustment',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'out' => 'Списание',
                        'adjustment' => 'Корректировка',
                    }),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('item.unit')
                    ->label('Ед. изм.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата операции')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип операции')
                    ->options([
                        'out' => 'Списание',
                        'adjustment' => 'Корректировка',
                    ]),

                Tables\Filters\SelectFilter::make('order_id')
                    ->label('Заказ')
                    ->options(Order::where('master_id', fn() => auth()->id())->pluck('order_number', 'id')),

                Tables\Filters\SelectFilter::make('item_id')
                    ->label('Товар/Материал')
                    ->options(InventoryItem::active()->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListInventoryTransactions::route('/'),
            'create' => Pages\CreateInventoryTransaction::route('/create'),
            'view' => Pages\ViewInventoryTransaction::route('/{record}'),
            'edit' => Pages\EditInventoryTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('order', function (Builder $query) {
                $query->where('master_id', fn() => auth()->id());
            })
            ->with(['order.client', 'order.serviceType', 'item']);
    }
}
