<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AnalyticsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 2;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $lastMonth = $now->copy()->subMonth();

        // Статистика за текущий месяц
        $currentMonthOrders = Order::whereBetween('created_at', [$monthStart, $monthEnd]);
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonth->startOfMonth(), $lastMonth->endOfMonth()]);

        $currentOrders = $currentMonthOrders->count();
        $lastOrders = $lastMonthOrders->count();
        $ordersGrowth = $lastOrders > 0 ? (($currentOrders - $lastOrders) / $lastOrders) * 100 : 0;

        $currentRevenue = $currentMonthOrders->sum('total_amount');
        $lastRevenue = $lastMonthOrders->sum('total_amount');
        $revenueGrowth = $lastRevenue > 0 ? (($currentRevenue - $lastRevenue) / $lastRevenue) * 100 : 0;

        $currentProfit = $currentMonthOrders->sum('profit');
        $lastProfit = $lastMonthOrders->sum('profit');
        $profitGrowth = $lastProfit > 0 ? (($currentProfit - $lastProfit) / $lastProfit) * 100 : 0;

        // Средняя маржинальность
        $avgMargin = $currentRevenue > 0 ? ($currentProfit / $currentRevenue) * 100 : 0;

        // Частота посещений клиентов
        $frequentClients = Client::whereHas('orders', function ($query) use ($monthStart, $monthEnd) {
            $query->whereBetween('created_at', [$monthStart, $monthEnd]);
        }, '>=', 2)->count();

        return [
            Stat::make('Заказов в этом месяце', $currentOrders)
                ->description($ordersGrowth >= 0 ? "+{$ordersGrowth}%" : "{$ordersGrowth}%")
                ->descriptionIcon($ordersGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color($ordersGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('Выручка за месяц', number_format($currentRevenue, 0, '.', ' ') . ' ₽')
                ->description($revenueGrowth >= 0 ? "+{$revenueGrowth}%" : "{$revenueGrowth}%")
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([4, 5, 3, 7, 4, 5, 6, 5])
                ->color($revenueGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('Прибыль за месяц', number_format($currentProfit, 0, '.', ' ') . ' ₽')
                ->description($profitGrowth >= 0 ? "+{$profitGrowth}%" : "{$profitGrowth}%")
                ->descriptionIcon($profitGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([3, 5, 7, 4, 5, 3, 7, 4])
                ->color($profitGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('Средняя маржинальность', number_format($avgMargin, 1) . '%')
                ->description('Прибыльность услуг')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([3, 5, 7, 4, 5, 3, 7, 4])
                ->color($avgMargin >= 30 ? 'success' : ($avgMargin >= 20 ? 'warning' : 'danger')),

            Stat::make('Постоянные клиенты', $frequentClients)
                ->description('2+ заказов в месяц')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('info'),
        ];
    }
}
