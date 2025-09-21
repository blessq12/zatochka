<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\WarehouseResource\Pages;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Склад';
    protected static ?string $pluralLabel = 'Склады';
    protected static ?string $modelLabel = 'Склад';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название склада')
                            ->required()
                            ->maxLength(255),


                        Forms\Components\Select::make('branch_id')
                            ->label('Филиал')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активный')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Описание')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
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

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Филиал')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активный')
                    ->boolean(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stock_items_count')
                    ->label('Товаров на складе')
                    ->counts('stockItems')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Филиал')
                    ->relationship('branch', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активный')
                    ->boolean()
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные')
                    ->native(false),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'view' => Pages\ViewWarehouse::route('/{record}'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
