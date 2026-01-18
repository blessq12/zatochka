<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UrgentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    public function getHeading(): string
    {
        return 'Срочные заказы';
    }

    protected function getStats(): array
    {
        $baseQuery = Order::where('is_deleted', false)
            ->where('urgency', Order::URGENCY_URGENT);

        $urgentNew = (clone $baseQuery)
            ->whereIn('status', [
                Order::STATUS_NEW,
                Order::STATUS_CONSULTATION,
                Order::STATUS_DIAGNOSTIC,
            ])
            ->count();

        $urgentInWork = (clone $baseQuery)
            ->where('status', Order::STATUS_IN_WORK)
            ->count();

        $urgentWaitingParts = (clone $baseQuery)
            ->where('status', Order::STATUS_WAITING_PARTS)
            ->count();

        $urgentReady = (clone $baseQuery)
            ->where('status', Order::STATUS_READY)
            ->count();

        $totalUrgent = $baseQuery->count();

        return [
            Stat::make('Срочных новых', $urgentNew)
                ->description('Требуют обработки')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Срочных в работе', $urgentInWork)
                ->description('Активные')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Срочных ожидают запчасти', $urgentWaitingParts)
                ->description('Требуют запчастей')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Срочных готовых', $urgentReady)
                ->description('Готовы к выдаче')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Всего срочных', $totalUrgent)
                ->description('Всего в системе')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('danger'),
        ];
    }
}
