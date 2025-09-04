<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\StockItemResource\Pages;
use App\Models\StockItem as StockItemModel;
use App\Models\Warehouse;
use App\Models\StockCategory;
// ... existing code ...
use App\Domain\Inventory\ValueObjects\StockItemName;
use App\Domain\Inventory\ValueObjects\SKU;
use App\Domain\Inventory\ValueObjects\Quantity;
use App\Domain\Inventory\ValueObjects\Money;
use App\Domain\Inventory\ValueObjects\Unit;
use App\Domain\Inventory\Services\StockItemService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StockItemResource extends Resource
{
    protected static ?string $model = StockItemModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Склад';

    protected static ?string $navigationLabel = 'Товары';

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товары';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Склад')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->placeholder('Выберите склад'),

                        Select::make('category_id')
                            ->label('Категория')
                            ->options(StockCategory::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->placeholder('Выберите категорию'),

                        TextInput::make('name')
                            ->label('Название товара')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Наименование товара'),

                        TextInput::make('sku')
                            ->label('Артикул (SKU)')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('ABC-123')
                            ->helperText('Уникальный артикул товара'),
                    ])
                    ->columns(2),

                Section::make('Описание и характеристики')
                    ->schema([
                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->placeholder('Подробное описание товара'),

                        TextInput::make('supplier')
                            ->label('Поставщик')
                            ->maxLength(255)
                            ->placeholder('Название поставщика'),

                        TextInput::make('manufacturer')
                            ->label('Производитель')
                            ->maxLength(255)
                            ->placeholder('Название производителя'),

                        TextInput::make('model')
                            ->label('Модель')
                            ->maxLength(255)
                            ->placeholder('Модель товара'),
                    ])
                    ->columns(2),

                Section::make('Цены')
                    ->schema([
                        TextInput::make('purchase_price')
                            ->label('Закупочная цена')
                            ->numeric()
                            ->prefix('₽')
                            ->placeholder('0.00')
                            ->helperText('Цена закупки товара'),

                        TextInput::make('retail_price')
                            ->label('Розничная цена')
                            ->numeric()
                            ->prefix('₽')
                            ->placeholder('0.00')
                            ->helperText('Цена продажи товара'),
                    ])
                    ->columns(2),

                Section::make('Остатки и единицы')
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Количество на складе')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->placeholder('0'),

                        TextInput::make('min_stock')
                            ->label('Минимальный остаток')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->placeholder('0')
                            ->helperText('При достижении этого количества будет показано предупреждение'),

                        Select::make('unit')
                            ->label('Единица измерения')
                            ->options([
                                'шт' => 'Штуки',
                                'кг' => 'Килограммы',
                                'г' => 'Граммы',
                                'л' => 'Литры',
                                'мл' => 'Миллилитры',
                                'м' => 'Метры',
                                'см' => 'Сантиметры',
                                'мм' => 'Миллиметры',
                                'кв.м' => 'Квадратные метры',
                                'куб.м' => 'Кубические метры',
                                'компл' => 'Комплект',
                                'упак' => 'Упаковка',
                                'банка' => 'Банка',
                                'бутылка' => 'Бутылка',
                                'рулон' => 'Рулон',
                                'лист' => 'Лист',
                                'пачка' => 'Пачка',
                            ])
                            ->default('шт')
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Статус')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->helperText('Активные товары доступны для операций'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('warehouse.name')
                    ->label('Склад')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Категория')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Остаток')
                    ->sortable()
                    ->badge()
                    ->color(
                        fn(int $state): string =>
                        $state <= 0 ? 'danger' : ($state <= 10 ? 'warning' : 'success')
                    ),

                TextColumn::make('unit')
                    ->label('Ед.изм.')
                    ->badge(),

                TextColumn::make('purchase_price')
                    ->label('Закупочная цена')
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('retail_price')
                    ->label('Розничная цена')
                    ->money('RUB')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'success' => 'Активен',
                        'danger' => 'Неактивен',
                    ])
                    ->getStateUsing(
                        fn(Model $record): string =>
                        $record->is_active ? 'Активен' : 'Неактивен'
                    ),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('Склад')
                    ->options(Warehouse::pluck('name', 'id')),

                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->options(StockCategory::pluck('name', 'id')),

                TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все товары')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),

                Filter::make('low_stock')
                    ->label('Низкий остаток')
                    ->query(fn(Builder $query): Builder => $query->whereRaw('quantity <= min_stock')),

                Filter::make('out_of_stock')
                    ->label('Нет в наличии')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', '<=', 0)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (array $data, Model $record): Model {
                        $stockItemService = app(StockItemService::class);

                        $stockItemId = (int) $record->id;

                        // Обновляем цены
                        if (
                            $data['purchase_price'] !== $record->purchase_price ||
                            $data['retail_price'] !== $record->retail_price
                        ) {
                            $purchasePrice = $data['purchase_price'] ? Money::fromFloat($data['purchase_price']) : null;
                            $retailPrice = $data['retail_price'] ? Money::fromFloat($data['retail_price']) : null;
                            $stockItemService->updatePrices($stockItemId, $purchasePrice, $retailPrice);
                        }

                        // Обновляем минимальный остаток
                        if ($data['min_stock'] !== $record->min_stock) {
                            $minStock = Quantity::fromInteger($data['min_stock']);
                            $stockItemService->updateMinStock($stockItemId, $minStock);
                        }

                        // Обновляем количество
                        if ($data['quantity'] !== $record->quantity) {
                            $newQuantity = Quantity::fromInteger($data['quantity']);
                            $stockItemService->setStock($stockItemId, $newQuantity);
                        }

                        // Активация/деактивация
                        if ($data['is_active'] && !$record->is_active) {
                            $stockItemService->activateStockItem($stockItemId);
                        } elseif (!$data['is_active'] && $record->is_active) {
                            $stockItemService->deactivateStockItem($stockItemId);
                        }

                        Notification::make()
                            ->title('Товар обновлен')
                            ->success()
                            ->send();

                        return $record->fresh();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->using(function (Model $record): void {
                        $stockItemService = app(StockItemService::class);
                        $stockItemId = (int) $record->id;

                        $stockItemService->deleteStockItem($stockItemId);

                        Notification::make()
                            ->title('Товар удален')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->using(function (array $records): void {
                            $stockItemService = app(StockItemService::class);

                            foreach ($records as $record) {
                                $stockItemId = (int) $record->id;
                                $stockItemService->deleteStockItem($stockItemId);
                            }

                            Notification::make()
                                ->title('Товары удалены')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockItems::route('/'),
            'create' => Pages\CreateStockItem::route('/create'),
            'edit' => Pages\EditStockItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['warehouse', 'category'])
            ->where('is_deleted', false);
    }
}
