<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\Actions\OrderManageActions;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function resolveRecord(int | string $key): Model
    {
        return static::getResource()::getEloquentQuery()
            ->with(['works', 'materials'])
            ->findOrFail($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            OrderManageActions::assignMaster(),
            OrderManageActions::linkEquipment(),
            OrderManageActions::printReceipt(),
            OrderManageActions::printHandoverAct(),
            OrderManageActions::setWorkPrices(),
            OrderManageActions::addMaterial(),
            OrderManageActions::removeMaterial(),
            OrderManageActions::recalculatePrice(),
            OrderManageActions::issue(),
            OrderManageActions::cancel(),
        ];
    }
}
