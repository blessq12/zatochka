<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MasterStats extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();

        return [
            Stat::make('Заказов сегодня', Order::whereDate('created_at', $today)->count())
                ->description('Новых заказов за сегодня')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('В работе', Order::where('status', 'in_work')->count())
                ->description('Заказов в работе')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning'),

            Stat::make('Готово к выдаче', Order::where('status', 'ready')->count())
                ->description('Заказов готово')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Передано курьеру', Order::where('status', 'courier_delivery')->count())
                ->description('Заказов у курьера')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
        ];
    }
}
