<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\RevenuePlan;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class MonthlyRevenuePlanWidget extends Widget
{
    protected static string $view = 'filament.widgets.monthly-revenue-plan-widget';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected ?array $cachedMetrics = null;

    public function getMetrics(): array
    {
        if ($this->cachedMetrics !== null) {
            return $this->cachedMetrics;
        }

        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        // Фактическая выручка за месяц (по выданным заказам), считаем по calculated_price
        $revenue = Order::where('is_deleted', false)
            ->where('status', Order::STATUS_ISSUED)
            ->where('updated_at', '>=', $monthStart)
            ->get()
            ->sum(fn (Order $order) => $order->calculated_price);

        // План на месяц: сначала общий (branch_id IS NULL), иначе первый по любому филиалу
        $planQuery = RevenuePlan::query()
            ->where('year', $now->year)
            ->where('month', $now->month);

        $plan = (clone $planQuery)
            ->whereNull('branch_id')
            ->first()
            ?? $planQuery->first();

        $hasPlan = $plan !== null;
        $targetAmount = $hasPlan ? (float) ($plan->target_amount ?? 0) : 0.0;

        $progress = $hasPlan && $targetAmount > 0
            ? (int) min(100, round(($revenue / $targetAmount) * 100))
            : 0;

        $monthLabel = $now->translatedFormat('F Y');

        $this->cachedMetrics = [
            'monthLabel' => $monthLabel,
            'hasPlan' => $hasPlan,
            'planFormatted' => $hasPlan ? number_format($targetAmount, 0, ',', ' ') . ' ₽' : null,
            'revenueFormatted' => number_format($revenue, 0, ',', ' ') . ' ₽',
            'progress' => $progress,
        ];

        return $this->cachedMetrics;
    }
}

