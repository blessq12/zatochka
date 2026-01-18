<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class RevenueStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Финансовая статистика';
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        $baseQuery = Order::where('is_deleted', false)
            ->where('status', Order::STATUS_READY);

        $todayRevenue = (clone $baseQuery)
            ->where('updated_at', '>=', $todayStart)
            ->sum('actual_price') ?? 0;

        $weekRevenue = (clone $baseQuery)
            ->where('updated_at', '>=', $weekStart)
            ->sum('actual_price') ?? 0;

        $monthRevenue = (clone $baseQuery)
            ->where('updated_at', '>=', $monthStart)
            ->sum('actual_price') ?? 0;

        $todayCount = (clone $baseQuery)
            ->where('updated_at', '>=', $todayStart)
            ->count();

        $weekCount = (clone $baseQuery)
            ->where('updated_at', '>=', $weekStart)
            ->count();

        $monthCount = (clone $baseQuery)
            ->where('updated_at', '>=', $monthStart)
            ->count();

        $todayAverage = $todayCount > 0 ? $todayRevenue / $todayCount : 0;
        $weekAverage = $weekCount > 0 ? $weekRevenue / $weekCount : 0;
        $monthAverage = $monthCount > 0 ? $monthRevenue / $monthCount : 0;

        return [
            Stat::make('Выручка за сегодня', number_format($todayRevenue, 0, ',', ' ') . ' ₽')
                ->description($todayCount . ' заказов, средний чек: ' . number_format($todayAverage, 0, ',', ' ') . ' ₽')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Выручка за неделю', number_format($weekRevenue, 0, ',', ' ') . ' ₽')
                ->description($weekCount . ' заказов, средний чек: ' . number_format($weekAverage, 0, ',', ' ') . ' ₽')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Выручка за месяц', number_format($monthRevenue, 0, ',', ' ') . ' ₽')
                ->description($monthCount . ' заказов, средний чек: ' . number_format($monthAverage, 0, ',', ' ') . ' ₽')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
