<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 2;

    protected function getStats(): array
    {
        return [
            Stat::make('Всего клиентов', Client::count())
                ->description('Активных клиентов в базе')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('success'),

            Stat::make('Активных заказов', Order::where('status', 'in_progress')->count())
                ->description('Заказов в работе')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->chart([4, 5, 3, 7, 4, 5, 6, 5])
                ->color('warning'),

            // Stat::make('Выручка за месяц', number_format(Order::whereMonth('created_at', now()->month)->sum('total_cost'), 0, '.', ' ') . ' ₽')
            //     ->description('+' . rand(5, 15) . '% с прошлого месяца')
            //     ->descriptionIcon('heroicon-m-currency-ruble')
            //     ->chart([3, 5, 7, 4, 5, 3, 7, 4])
            //     ->color('success'),
        ];
    }
}
