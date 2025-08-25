<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderToolResource\Pages;
use App\Models\OrderTool;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderToolResource extends Resource
{
    protected static ?string $model = OrderTool::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Инструменты в заказах';
    protected static ?int $navigationSort = 9;
    protected static ?string $breadcrumb = 'Инструменты в заказах';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('tool_id')
                            ->label('Инструмент')
                            ->relationship('tool', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->required()
                            ->default(1),
                    ])->columns(3),

                Forms\Components\Section::make('Финансы')
                    ->schema([
                        Forms\Components\TextInput::make('cost_price')
                            ->label('Себестоимость')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('profit')
                            ->label('Прибыль')
                            ->numeric()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('№ заказа')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tool.name')
                    ->label('Инструмент')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Количество')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Себестоимость')
                    ->money('RUB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('profit')
                    ->label('Прибыль')
                    ->money('RUB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Общая себестоимость')
                    ->money('RUB')
                    ->getStateUsing(fn(OrderTool $record): float => $record->cost_price * $record->quantity)
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_profit')
                    ->label('Общая прибыль')
                    ->money('RUB')
                    ->getStateUsing(fn(OrderTool $record): float => $record->profit * $record->quantity)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_id')
                    ->label('Заказ')
                    ->relationship('order', 'order_number')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('tool_id')
                    ->label('Инструмент')
                    ->relationship('tool', 'name')
                    ->searchable()
                    ->preload(),
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
            // Можно добавить relations если нужно
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderTools::route('/'),
            'create' => Pages\CreateOrderTool::route('/create'),
            'edit' => Pages\EditOrderTool::route('/{record}/edit'),
            'view' => Pages\ViewOrderTool::route('/{record}'),
        ];
    }
}
