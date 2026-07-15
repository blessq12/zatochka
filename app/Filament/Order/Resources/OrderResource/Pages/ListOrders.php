<?php

namespace App\Filament\Order\Resources\OrderResource\Pages;

use App\Filament\Order\Resources\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Заказы';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Создать заказ'),
        ];
    }
}
