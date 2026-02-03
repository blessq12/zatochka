<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderWorkMaterial;
use App\Models\WarehouseItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Eloquent\Builder;

class OrderMaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderMaterials';

    protected static ?string $title = 'Материалы';

    protected static ?string $modelLabel = 'Материал';

    protected static ?string $pluralModelLabel = 'Материалы';

    protected static string $model = OrderWorkMaterial::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('warehouse_item_id')
                    ->label('Товар со склада')
                    ->options(
                        WarehouseItem::where('is_active', true)
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn($item) => [$item->id => $item->name . ($item->article ? " ({$item->article})" : '')])
                    )
                    ->getOptionLabelUsing(fn($value): ?string => WarehouseItem::find($value)?->name ?? ($value ? "ID: {$value}" : null))
                    ->searchable()
                    ->required()
                    ->live()
                    ->visible(fn(?OrderWorkMaterial $record) => $record === null)
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                        if ($state && $item = WarehouseItem::with('category')->find($state)) {
                            $set('name', $item->name);
                            $set('article', $item->article);
                            $set('category_name', $item->category?->name);
                            $set('unit', $item->unit ?? 'шт');
                            $set('price', $item->price ?? 0);
                        }
                    }),

                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255)
                    ->visible(fn(?OrderWorkMaterial $record) => $record === null),

                Forms\Components\TextInput::make('article')
                    ->label('Артикул')
                    ->maxLength(255)
                    ->visible(fn(?OrderWorkMaterial $record) => $record === null),

                Forms\Components\TextInput::make('quantity')
                    ->label('Количество')
                    ->required()
                    ->numeric()
                    ->minValue(0.001)
                    ->step(0.001),

                Forms\Components\TextInput::make('price')
                    ->label('Цена за ед.')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->prefix('₽'),

                Forms\Components\Textarea::make('notes')
                    ->label('Примечания')
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->visible(fn(?OrderWorkMaterial $record) => $record === null),
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
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['order_id'] = $this->getOwnerRecord()->id;
                        $data['work_id'] = null;
                        if (isset($data['warehouse_item_id']) && $item = WarehouseItem::with('category')->find($data['warehouse_item_id'])) {
                            $data['name'] = $item->name;
                            $data['article'] = $item->article;
                            $data['category_name'] = $item->category?->name;
                            $data['unit'] = $item->unit ?? 'шт';
                            $data['price'] = $data['price'] ?? $item->price ?? 0;
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Редактировать')
                    ->mutateFormDataUsing(function (array $data, OrderWorkMaterial $record): array {
                        return [
                            'price' => $data['price'] ?? $record->price,
                            'quantity' => $data['quantity'] ?? $record->quantity,
                        ];
                    }),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Удалить')
                    ->requiresConfirmation(),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) use ($orderId) {
                // Получаем все материалы заказа независимо от работ
                return $query
                    ->where('order_id', $orderId)
                    ->with('warehouseItem');
            })
            ->emptyStateHeading('Материалы не добавлены')
            ->emptyStateDescription('Материалы добавляются в POS мастерской')
            ->emptyStateIcon('heroicon-o-cube');
    }
}
