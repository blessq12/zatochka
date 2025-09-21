<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\StockCategoryResource\Pages;
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

    protected static ?string $pluralModelLabel = 'Категории запчастей';

    protected static ?string $navigationGroup = 'Инвентарь';

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

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\ColorPicker::make('color')
                            ->label('Цвет')
                            ->default('#6B7280')
                            ->helperText('Цвет для отображения в интерфейсе'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Чем меньше число, тем выше в списке'),
                    ]),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),

                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удалена')
                            ->default(false),
                    ])
                    ->collapsible(),
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

                        return $state && strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stock_items_count')
                    ->label('Товаров')
                    ->counts('stockItems')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_value')
                    ->label('Стоимость товаров')
                    ->getStateUsing(fn(StockCategory $record): float => $record->getTotalValue())
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Статус')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Активна' : 'Неактивна'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удалена')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все категории')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),

                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Удаленные')
                    ->placeholder('Все категории')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),

                Tables\Filters\Filter::make('has_stock_items')
                    ->label('С товарами')
                    ->query(fn(Builder $query): Builder => $query->has('stockItems')),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Низкие запасы')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('stockItems', function (Builder $query) {
                            $query->whereRaw('quantity <= min_stock')
                                ->where('is_active', true);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_stock_items')
                    ->label('Товары')
                    ->icon('heroicon-o-cube')
                    ->url(fn(StockCategory $record): string => route('filament.manager.resources.manager.stock-items.index', ['tableFilters[category_id][value]' => $record->id])),

                Tables\Actions\Action::make('low_stock_report')
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
                            $html .= '</div>';
                        }
                        $html .= '</div>';

                        return $html;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Удалить категорию')
                    ->modalDescription(function (StockCategory $record): string {
                        $itemsCount = $record->stockItems()->count();

                        if ($itemsCount > 0) {
                            return "В этой категории находится {$itemsCount} запчастей. При удалении категории все запчасти также будут удалены. Это действие нельзя отменить.";
                        }

                        return 'Вы уверены, что хотите удалить эту категорию?';
                    })
                    ->modalSubmitActionLabel('Удалить')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Удалить выбранные категории')
                        ->modalDescription(function ($records): string {
                            $totalItems = 0;
                            foreach ($records as $record) {
                                $totalItems += $record->stockItems()->count();
                            }

                            if ($totalItems > 0) {
                                return "В выбранных категориях находится {$totalItems} запчастей. При удалении категорий все запчасти также будут удалены. Это действие нельзя отменить.";
                            }

                            return 'Вы уверены, что хотите удалить выбранные категории?';
                        })
                        ->modalSubmitActionLabel('Удалить')
                        ->color('danger'),
                    Tables\Actions\BulkAction::make('mark_deleted')
                        ->label('Пометить как удаленные')
                        ->icon('heroicon-o-trash')
                        ->action(function ($records): void {
                            $records->each->update(['is_deleted' => true]);
                            \Filament\Notifications\Notification::make()
                                ->title('Категории помечены как удаленные')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Активировать')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records): void {
                            $records->each->activate();
                            \Filament\Notifications\Notification::make()
                                ->title('Категории активированы')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Деактивировать')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function ($records): void {
                            $records->each->deactivate();
                            \Filament\Notifications\Notification::make()
                                ->title('Категории деактивированы')
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'create' => Pages\CreateStockCategory::route('/create'),
            'view' => Pages\ViewStockCategory::route('/{record}'),
            'edit' => Pages\EditStockCategory::route('/{record}/edit'),
        ];
    }
}
