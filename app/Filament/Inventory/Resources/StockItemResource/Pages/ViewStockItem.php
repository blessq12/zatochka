<?php

namespace App\Filament\Inventory\Resources\StockItemResource\Pages;

use App\Filament\Inventory\Resources\StockItemResource;
use App\Infrastructure\Inventory\Model\StockItemModel;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewStockItem extends ViewRecord
{
    protected static string $resource = StockItemResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecordTitle();
    }

    public function getRecordTitle(): string|Htmlable
    {
        $record = $this->getRecord();

        if ($record instanceof StockItemModel) {
            $record->loadMissing('material');

            if (filled($record->material?->name)) {
                return (string) $record->material->name;
            }
        }

        return StockItemResource::getRecordTitle($record) ?? parent::getRecordTitle();
    }

    protected function getHeaderActions(): array
    {
        return array_map(
            function ($action) {
                return $action->after(function (): void {
                    $this->getRecord()->refresh()->load(['material', 'movements']);
                });
            },
            StockItemResource::stockMutationActions(),
        );
    }
}
