<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ServiceTypeStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return 'Статистика по типам услуг';
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        $baseQuery = Order::where('is_deleted', false)
            ->where('created_at', '>=', $monthStart);

        $repairCount = (clone $baseQuery)
            ->where('service_type', Order::TYPE_REPAIR)
            ->count();

        $sharpeningCount = (clone $baseQuery)
            ->where('service_type', Order::TYPE_SHARPENING)
            ->count();

        $diagnosticCount = (clone $baseQuery)
            ->where('service_type', Order::TYPE_DIAGNOSTIC)
            ->count();

        $replacementCount = (clone $baseQuery)
            ->where('service_type', Order::TYPE_REPLACEMENT)
            ->count();

        $maintenanceCount = (clone $baseQuery)
            ->where('service_type', Order::TYPE_MAINTENANCE)
            ->count();

        $warrantyCount = (clone $baseQuery)
            ->where('service_type', Order::TYPE_WARRANTY)
            ->count();

        return [
            Stat::make('Ремонт', $repairCount)
                ->description('За этот месяц')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('primary'),

            Stat::make('Заточка', $sharpeningCount)
                ->description('За этот месяц')
                ->descriptionIcon('heroicon-m-scissors')
                ->color('info'),

            Stat::make('Диагностика', $diagnosticCount)
                ->description('За этот месяц')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('warning'),

            Stat::make('Замена', $replacementCount)
                ->description('За этот месяц')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('success'),

            Stat::make('Обслуживание', $maintenanceCount)
                ->description('За этот месяц')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('gray'),

            Stat::make('Гарантийный', $warrantyCount)
                ->description('За этот месяц')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('danger'),
        ];
    }
}
