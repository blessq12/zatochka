<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderWork;
use App\Models\WarehouseItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderMaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderMaterials';

    protected static ?string $title = 'Материалы';

    protected static ?string $modelLabel = 'Материал';

    protected static ?string $pluralModelLabel = 'Материалы';

    protected static string $model = \App\Models\OrderWorkMaterial::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Форма не используется, так как материалы добавляются через работы
            ]);
    }

    public function table(Table $table): Table
    {
        $orderId = $this->getOwnerRecord()->id;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название материала')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('article')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->copyable(),

                Tables\Columns\TextColumn::make('category_name')
                    ->label('Категория')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(fn($record) => ' ' . ($record->unit ?? 'шт'))
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена за ед.')
                    ->money('RUB')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Сумма')
                    ->money('RUB')
                    ->getStateUsing(function ($record) {
                        return (float) ($record->quantity * $record->price);
                    })
                    ->sortable()
                    ->alignRight()
                    ->weight('bold')
                    ->summarize([
                        Summarizer::make('grand_total')
                            ->label('Итого')
                            ->money('RUB')
                            ->using(function ($query) {
                                return $query
                                    ->selectRaw('SUM(quantity * price) as total_sum')
                                    ->value('total_sum');
                            }),
                    ]),

                Tables\Columns\IconColumn::make('warehouse_item_id')
                    ->label('На складе')
                    ->boolean()
                    ->getStateUsing(fn($record) => !is_null($record->warehouse_item_id))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn($record) => $record->warehouse_item_id 
                        ? 'Товар существует на складе' 
                        : 'Товар удален со склада или не найден')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Примечания')
                    ->wrap()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                // Материалы добавляются через работы, поэтому здесь нет действий
            ])
            ->actions([
                // Материалы редактируются через работы
            ])
            ->bulkActions([
                // Нет массовых действий
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) use ($orderId) {
                // Получаем все материалы заказа независимо от работ
                return $query
                    ->where('order_id', $orderId)
                    ->with('warehouseItem');
            })
            ->emptyStateHeading('Материалы не добавлены')
            ->emptyStateDescription('Материалы добавляются через работы заказа')
            ->emptyStateIcon('heroicon-o-cube');
    }
}
