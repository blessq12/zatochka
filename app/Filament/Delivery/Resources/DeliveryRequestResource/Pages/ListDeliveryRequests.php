<?php

namespace App\Filament\Delivery\Resources\DeliveryRequestResource\Pages;

use App\Filament\Delivery\Resources\DeliveryRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryRequests extends ListRecords
{
    protected static string $resource = DeliveryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return DeliveryRequestResource::getHeaderActions();
    }
}
