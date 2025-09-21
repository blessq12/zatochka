<?php

namespace App\Filament\Resources\Master\OrderResource\Pages;

use App\Filament\Resources\Master\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return OrderResource::infolist($infolist);
    }
}
