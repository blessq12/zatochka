<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\StockCategoryResource\Pages;
use App\Models\StockCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockCategoryResource extends Resource
{
    protected static ?string $model = StockCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Категории запчастей';

    protected static ?string $modelLabel = 'Категория';

    protected static ?string $pluralModelLabel = 'Категории';

    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->disabled(),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\ColorPicker::make('color')
                            ->label('Цвет')
                            ->disabled(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->disabled(),

                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удалена')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label('Цвет')
                    ->copyable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('stock_items_count')
                    ->label('Запчастей')
                    ->counts('stockItems')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_stock_value')
                    ->label('Стоимость остатков')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все категории')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),

                Tables\Filters\Filter::make('has_low_stock_items')
                    ->label('С низкими запасами')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('stockItems', function (Builder $query) {
                            $query->whereColumn('quantity', '<=', 'min_stock');
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('view_stock_items_in_category')
                    ->label('Запчасти')
                    ->icon('heroicon-o-cube')
                    ->url(fn(StockCategory $record): string => route('filament.master.resources.master.stock-items.index', ['tableFilters[category_id][value]' => $record->id])),

                Tables\Actions\Action::make('low_stock_report_in_category')
                    ->label('Низкие запасы')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->modalContent(function (StockCategory $record): string {
                        $lowStockItems = $record->getLowStockItems();

                        if ($lowStockItems->isEmpty()) {
                            return '<p>Нет товаров с низкими запасами в этой категории.</p>';
                        }

                        $html = '<div class="space-y-2">';
                        foreach ($lowStockItems as $item) {
                            $html .= "<div class='p-2 bg-yellow-50 rounded'>";
                            $html .= "<strong>{$item->name}</strong><br>";
                            $html .= "Остаток: {$item->quantity} / Минимум: {$item->min_stock}";
                            $html .= "</div>";
                        }
                        $html .= '</div>';

                        return $html;
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('sort_order');
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
            'index' => Pages\ListStockCategories::route('/'),
            'view' => Pages\ViewStockCategory::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
