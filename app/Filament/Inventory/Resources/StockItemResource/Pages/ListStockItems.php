<?php

namespace App\Filament\Inventory\Resources\StockItemResource\Pages;

use App\Domain\Inventory\VO\StockCategory;
use App\Filament\Inventory\Resources\StockItemResource;
use App\Infrastructure\Inventory\Model\StockItemModel;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStockItems extends ListRecords
{
    protected static string $resource = StockItemResource::class;

    protected static ?string $title = 'Склад';

    protected function getHeaderActions(): array
    {
        return StockItemResource::getHeaderActions();
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Все')
                ->badge(fn (): int => StockItemModel::query()->count()),
            StockCategory::Consumable->value => Tab::make('Расходные материалы')
                ->badge(fn (): int => $this->categoryCount(StockCategory::Consumable))
                ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyCategoryFilter($query, StockCategory::Consumable)),
            StockCategory::SparePart->value => Tab::make('Запчасти')
                ->badge(fn (): int => $this->categoryCount(StockCategory::SparePart))
                ->modifyQueryUsing(fn (Builder $query): Builder => $this->applyCategoryFilter($query, StockCategory::SparePart)),
        ];
    }

    private function applyCategoryFilter(Builder $query, StockCategory $category): Builder
    {
        return $query->whereHas(
            'material',
            fn (Builder $material): Builder => $material->where('category', $category->value),
        );
    }

    private function categoryCount(StockCategory $category): int
    {
        return StockItemModel::query()
            ->whereHas(
                'material',
                fn (Builder $material): Builder => $material->where('category', $category->value),
            )
            ->count();
    }
}
