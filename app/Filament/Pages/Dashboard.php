<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Дашборд';

    public function getColumns(): int | array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\QuickActions::class,
            \App\Filament\Widgets\AnalyticsOverview::class,
            \App\Filament\Widgets\LatestOrders::class,
            \App\Filament\Widgets\PopularServices::class,
            \App\Filament\Widgets\ClientFrequency::class,
            \App\Filament\Widgets\OrdersChart::class,
        ];
    }
}
