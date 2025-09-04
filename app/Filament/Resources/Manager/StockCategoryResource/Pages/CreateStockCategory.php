<?php

namespace App\Filament\Resources\Manager\StockCategoryResource\Pages;

use App\Filament\Resources\Manager\StockCategoryResource;
// ... existing code ...
use App\Domain\Inventory\ValueObjects\CategoryName;
use App\Domain\Inventory\Services\StockCategoryService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateStockCategory extends CreateRecord
{
    protected static string $resource = StockCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function afterCreate(): void
    {
        $categoryService = app(StockCategoryService::class);

        $id = (int) $this->record->id;
        $name = CategoryName::fromString($this->record->name);

        // Создаем категорию через доменный сервис
        $category = $categoryService->createCategory(
            $id,
            $name,
            $this->record->description,
            $this->record->color,
            $this->record->sort_order ?? 0
        );

        Notification::make()
            ->title('Категория создана')
            ->success()
            ->send();
    }
}
