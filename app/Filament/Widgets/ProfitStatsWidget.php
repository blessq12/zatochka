<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderWork;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ProfitStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Прибыль и маржа';
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        $baseOrdersQuery = Order::where('is_deleted', false)
            ->where('status', Order::STATUS_ISSUED);

        $stats = [];

        foreach ([
            'today' => ['label' => 'За сегодня', 'start' => $todayStart],
            'week' => ['label' => 'За неделю', 'start' => $weekStart],
            'month' => ['label' => 'За месяц', 'start' => $monthStart],
        ] as $key => $config) {
            $start = $config['start'];

            $revenue = (clone $baseOrdersQuery)
                ->where('updated_at', '>=', $start)
                ->sum('price') ?? 0;

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

            $label = match ($key) {
                'today' => 'Прибыль за сегодня',
                'week' => 'Прибыль за неделю',
                'month' => 'Прибыль за месяц',
                default => 'Прибыль',
            };

            $stats[] = Stat::make(
                $label,
                number_format($profit, 0, ',', ' ') . ' ₽'
            )
                ->description('Маржа: ' . $margin . '%')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($profit >= 0 ? 'success' : 'danger');
        }

        return $stats;
    }
}

