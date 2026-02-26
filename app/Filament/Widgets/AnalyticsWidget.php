<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Аналитика';
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        $baseQuery = Order::where('is_deleted', false)
            ->where('created_at', '>=', $monthStart);

        // Статистика по типам услуг (топ-3)
        $repairCount = (clone $baseQuery)
            ->where('service_type', Order::TYPE_REPAIR)
            ->count();
        $sharpeningCount = (clone $baseQuery)
            ->where('service_type', Order::TYPE_SHARPENING)
            ->count();
        $diagnosticCount = (clone $baseQuery)
            ->where('service_type', Order::TYPE_DIAGNOSTIC)
            ->count();

        // Статистика по клиентам
        $totalClients = Client::where('is_deleted', false)->count();
        $newClientsMonth = Client::where('is_deleted', false)
            ->where('created_at', '>=', $monthStart)
            ->count();
        $activeClients = Order::where('is_deleted', false)
            ->where('created_at', '>=', $monthStart)
            ->distinct('client_id')
            ->count('client_id');

        return [
            // Типы услуг
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

            // Клиенты
            Stat::make('Всего клиентов', $totalClients)
                ->description('В базе данных')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Новых клиентов', $newClientsMonth)
                ->description('За этот месяц')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),

            Stat::make('Активных клиентов', $activeClients)
                ->description('С заказами за месяц')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info')
                ->url(
                    ClientResource::getUrl('index', [
                        'table[filters][has_orders_this_month][value]' => 1,
                    ])
                ),
        ];
    }
}
