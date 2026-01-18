<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompletedOrdersStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Завершенные заказы';
    }

    protected function getStats(): array
    {
        $baseQuery = Order::where('is_deleted', false);

        return [
            Stat::make('Готовых заказов', $baseQuery->clone()
                ->where('status', Order::STATUS_READY)
                ->count())
                ->description('Готовы к выдаче')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Выданных заказов', $baseQuery->clone()
                ->where('status', Order::STATUS_ISSUED)
                ->count())
                ->description('Завершены')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Отмененных заказов', $baseQuery->clone()
                ->where('status', Order::STATUS_CANCELLED)
                ->count())
                ->description('Не выполнены')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
