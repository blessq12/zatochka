<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\RevenuePlan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class RevenuePlanWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'План выручки';
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $quarterStart = $now->copy()->firstOfQuarter();

        $baseRevenueQuery = Order::where('is_deleted', false)
            ->where('status', Order::STATUS_ISSUED);

        $monthRevenue = (clone $baseRevenueQuery)
            ->where('updated_at', '>=', $monthStart)
            ->sum('price') ?? 0;

        $quarterRevenue = (clone $baseRevenueQuery)
            ->where('updated_at', '>=', $quarterStart)
            ->sum('price') ?? 0;

        $monthPlan = RevenuePlan::query()
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->whereNull('branch_id')
            ->first();

        $currentQuarter = (int) ceil($now->month / 3);
        $quarterMonths = range(($currentQuarter - 1) * 3 + 1, $currentQuarter * 3);

        $quarterPlanAmount = RevenuePlan::query()
            ->where('year', $now->year)
            ->whereIn('month', $quarterMonths)
            ->whereNull('branch_id')
            ->sum('target_amount');

        $stats = [];

        // Месячный план
        if ($monthPlan) {
            $monthProgress = $monthPlan->target_amount > 0
                ? min(100, round(($monthRevenue / $monthPlan->target_amount) * 100))
                : 0;

            $stats[] = Stat::make(
                'План на месяц',
                number_format($monthRevenue, 0, ',', ' ') . ' / ' . number_format((float) $monthPlan->target_amount, 0, ',', ' ') . ' ₽'
            )
                ->description("Достигнуто {$monthProgress}%")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($monthProgress >= 100 ? 'success' : 'primary')
                ->progress($monthProgress);
        } else {
            $stats[] = Stat::make('План на месяц', number_format($monthRevenue, 0, ',', ' ') . ' ₽')
                ->description('План не задан')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning');
        }

        // Квартальный план
        if ($quarterPlanAmount > 0) {
            $quarterProgress = min(100, round(($quarterRevenue / $quarterPlanAmount) * 100));

            $stats[] = Stat::make(
                'План на квартал',
                number_format($quarterRevenue, 0, ',', ' ') . ' / ' . number_format((float) $quarterPlanAmount, 0, ',', ' ') . ' ₽'
            )
                ->description("Достигнуто {$quarterProgress}%")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($quarterProgress >= 100 ? 'success' : 'primary')
                ->progress($quarterProgress);
        } else {
            $stats[] = Stat::make('План на квартал', number_format($quarterRevenue, 0, ',', ' ') . ' ₽')
                ->description('План не задан')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning');
        }

        return $stats;
    }
}

