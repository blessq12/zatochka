<?php

namespace App\Filament\Resources\Master\OrderResource\Pages;

use App\Filament\Resources\Master\OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
}
