<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ClientsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    public function getHeading(): string
    {
        return 'Статистика по клиентам';
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $monthStart = $now->copy()->startOfMonth();

        $totalClients = Client::where('is_deleted', false)->count();

        $newClientsToday = Client::where('is_deleted', false)
            ->whereDate('created_at', $todayStart)
            ->count();

        $newClientsMonth = Client::where('is_deleted', false)
            ->where('created_at', '>=', $monthStart)
            ->count();

        $activeClients = Order::where('is_deleted', false)
            ->where('created_at', '>=', $monthStart)
            ->distinct('client_id')
            ->count('client_id');

        $repeatClients = Order::where('is_deleted', false)
            ->where('created_at', '>=', $monthStart)
            ->select('client_id')
            ->groupBy('client_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        return [
            Stat::make('Всего клиентов', $totalClients)
                ->description('В базе данных')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Новых сегодня', $newClientsToday)
                ->description('Зарегистрировано')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),

            Stat::make('Новых за месяц', $newClientsMonth)
                ->description('Зарегистрировано')
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

            Stat::make('Постоянных клиентов', $repeatClients)
                ->description('С несколькими заказами')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }
}
