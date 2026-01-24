<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\OrderResource;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions-widget';

    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public function getCreateOrderUrl(): string
    {
        return OrderResource::getUrl('create');
    }

    public function getCreateClientUrl(): string
    {
        return ClientResource::getUrl('create');
    }
}
