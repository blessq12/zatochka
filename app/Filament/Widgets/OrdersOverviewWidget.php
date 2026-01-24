<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class OrdersOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Обзор заказов';
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        $baseQuery = Order::where('is_deleted', false);

        // Финансовая статистика
        $revenueQuery = (clone $baseQuery)->where('status', Order::STATUS_READY);
        $todayRevenue = (clone $revenueQuery)
            ->where('updated_at', '>=', $todayStart)
            ->sum('actual_price') ?? 0;
        $weekRevenue = (clone $revenueQuery)
            ->where('updated_at', '>=', $weekStart)
            ->sum('actual_price') ?? 0;
        $monthRevenue = (clone $revenueQuery)
            ->where('updated_at', '>=', $monthStart)
            ->sum('actual_price') ?? 0;

        // Активные заказы
        $newOrders = (clone $baseQuery)
            ->whereIn('status', [
                Order::STATUS_NEW,
                Order::STATUS_CONSULTATION,
                Order::STATUS_DIAGNOSTIC,
            ])
            ->count();
        $inWork = (clone $baseQuery)
            ->where('status', Order::STATUS_IN_WORK)
            ->count();
        $waitingParts = (clone $baseQuery)
            ->where('status', Order::STATUS_WAITING_PARTS)
            ->count();

        // Завершенные заказы
        $ready = (clone $baseQuery)
            ->where('status', Order::STATUS_READY)
            ->count();
        $issued = (clone $baseQuery)
            ->where('status', Order::STATUS_ISSUED)
            ->count();

        // Срочные заказы
        $urgentTotal = (clone $baseQuery)
            ->where('urgency', Order::URGENCY_URGENT)
            ->count();

        return [
            // Финансы
            Stat::make('Выручка за месяц', number_format($monthRevenue, 0, ',', ' ') . ' ₽')
                ->description('Завершенные заказы')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            // Активные заказы
            Stat::make('Новых заказов', $newOrders)
                ->description('Требуют обработки')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('В работе', $inWork)
                ->description('Активные заказы')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning'),

            Stat::make('Ожидание запчастей', $waitingParts)
                ->description('Требуют запчастей')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // Завершенные
            Stat::make('Готовых к выдаче', $ready)
                ->description('Ожидают выдачи')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Выдано заказов', $issued)
                ->description('Завершены')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            // Срочные
            Stat::make('Срочных заказов', $urgentTotal)
                ->description('Требуют внимания')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
