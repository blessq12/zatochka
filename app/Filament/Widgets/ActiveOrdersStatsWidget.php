<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActiveOrdersStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Активные заказы';
    }

    protected function getStats(): array
    {
        $baseQuery = Order::where('is_deleted', false);

        return [
            Stat::make('Новых заказов', $baseQuery->clone()
                ->whereIn('status', [
                    Order::STATUS_NEW,
                ])
                ->count())
                ->description('Требуют обработки')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('В работе', $baseQuery->clone()
                ->where('status', Order::STATUS_IN_WORK)
                ->count())
                ->description('Активные заказы')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning'),

            Stat::make('Ожидание запчастей', $baseQuery->clone()
                ->where('status', Order::STATUS_WAITING_PARTS)
                ->count())
                ->description('Требуют запчастей')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
