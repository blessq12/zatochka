<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderWork;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FinancialOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

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

        $ordersQuery = Order::where('is_deleted', false)
            ->where('status', Order::STATUS_ISSUED);

        // Прибыль / маржа
        $profitStats = $this->calculateProfitStats($ordersQuery, $todayStart, $weekStart, $monthStart);

        $stats = [];

        // Выручка и средний чек (сегодня / неделя / месяц)
        $revenueStats = $this->calculateRevenueStats($ordersQuery, $todayStart, $weekStart, $monthStart);
        $stats = array_merge($stats, $revenueStats);

        // Прибыль и маржа (месяц)
        $monthProfit = $profitStats['month']['profit'] ?? 0;
        $monthMargin = $profitStats['month']['margin'] ?? 0;

        $stats[] = Stat::make(
            'Прибыль за месяц',
            number_format($monthProfit, 0, ',', ' ') . ' ₽'
        )
            ->description('Маржа: ' . $monthMargin . '%')
            ->descriptionIcon('heroicon-m-banknotes')
            ->color($monthProfit >= 0 ? 'success' : 'danger');

        return $stats;
    }

    /**
     * Выручка и средний чек за периоды
     */
    protected function calculateRevenueStats($baseQuery, Carbon $todayStart, Carbon $weekStart, Carbon $monthStart): array
    {
        $stats = [];

        foreach ([
            'today' => ['label' => 'Выручка за сегодня', 'start' => $todayStart],
            'week' => ['label' => 'Выручка за неделю', 'start' => $weekStart],
            'month' => ['label' => 'Выручка за месяц', 'start' => $monthStart],
        ] as $key => $config) {
            $start = $config['start'];

            $orders = (clone $baseQuery)
                ->where('updated_at', '>=', $start)
                ->get();

            $revenue = $orders->sum(fn (Order $order) => $order->calculated_price);
            $count = $orders->count();

            $average = $count > 0 ? $revenue / $count : 0;

            $stats[] = Stat::make(
                $config['label'],
                number_format($revenue, 0, ',', ' ') . ' ₽'
            )
                ->description(
                    $count . ' заказов, средний чек: ' . number_format($average, 0, ',', ' ') . ' ₽'
                )
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success');
        }

        return $stats;
    }

    /**
     * Прибыль и маржа за периоды
     */
    protected function calculateProfitStats($ordersQuery, Carbon $todayStart, Carbon $weekStart, Carbon $monthStart): array
    {
        $result = [];

        foreach ([
            'today' => $todayStart,
            'week' => $weekStart,
            'month' => $monthStart,
        ] as $key => $start) {
            $orders = (clone $ordersQuery)
                ->where('updated_at', '>=', $start)
                ->get();

            $revenue = $orders->sum(fn (Order $order) => $order->calculated_price);

            $materialsCost = OrderWork::query()
                ->where('is_deleted', false)
                ->whereHas('order', function ($query) use ($start) {
                    $query
                        ->where('is_deleted', false)
                        ->where('status', Order::STATUS_ISSUED)
                        ->where('updated_at', '>=', $start);
                })
                ->sum('materials_cost') ?? 0;

            $profit = $revenue - $materialsCost;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100) : 0;

            $result[$key] = [
                'revenue' => $revenue,
                'profit' => $profit,
                'margin' => $margin,
            ];
        }

        return $result;
    }
}

