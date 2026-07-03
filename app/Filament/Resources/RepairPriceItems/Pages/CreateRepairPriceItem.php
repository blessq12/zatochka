<?php

namespace App\Filament\Resources\RepairPriceItems\Pages;

use App\Filament\Resources\PriceItems\Pages\CreatePriceItemRecord;
use App\Filament\Resources\RepairPriceItems\RepairPriceItemResource;

class CreateRepairPriceItem extends CreatePriceItemRecord
{
    protected static string $resource = RepairPriceItemResource::class;
}
