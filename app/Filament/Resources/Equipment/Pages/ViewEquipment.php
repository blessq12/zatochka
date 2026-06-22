<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class ViewEquipment extends ViewRecord
{
    protected static string $resource = EquipmentResource::class;

    public function resolveRecord(int|string $key): Model
    {
        return static::getResource()::getEloquentQuery()
            ->with('orders')
            ->findOrFail($key);
    }

    public function getTitle(): string|Htmlable
    {
        /** @var EquipmentModel $record */
        $record = $this->getRecord();

        return $record->name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        /** @var EquipmentModel $record */
        $record = $this->getRecord();

        $parts = array_filter([$record->brand, $record->model]);

        return $parts !== [] ? implode(' · ', $parts) : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
