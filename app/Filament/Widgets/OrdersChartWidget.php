<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrdersChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Динамика заказов';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $now = Carbon::now();
        $days = [];
        $completed = [];
        $inWork = [];

        // Получаем данные за последние 7 дней
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $days[] = $date->format('d.m');
            
            $dateStart = $date->copy()->startOfDay();
            $dateEnd = $date->copy()->endOfDay();

            $completed[] = Order::where('is_deleted', false)
                ->where('status', Order::STATUS_READY)
                ->whereBetween('updated_at', [$dateStart, $dateEnd])
                ->count();

            $inWork[] = Order::where('is_deleted', false)
                ->where('status', Order::STATUS_IN_WORK)
                ->whereBetween('created_at', [$dateStart, $dateEnd])
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Завершено',
                    'data' => $completed,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'fill' => true,
                ],
                [
                    'label' => 'В работе',
                    'data' => $inWork,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.1)',
                    'borderColor' => 'rgb(251, 191, 36)',
                    'fill' => true,
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
