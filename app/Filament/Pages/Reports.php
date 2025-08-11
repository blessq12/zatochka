<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Client;
use App\Models\OrderTool;
use App\Models\Repair;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\DB;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    // protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 7;
    protected static ?string $title = 'Отчеты';

    protected static string $view = 'filament.pages.reports';

    public ?array $data = [];
    public string $period = 'month';

    public function mount(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $query = match ($this->period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        // Общая статистика
        $this->data['total_orders'] = Order::where('created_at', '>=', $query)->count();
        $this->data['total_revenue'] = Order::where('created_at', '>=', $query)->sum('total_amount');
        $this->data['total_profit'] = Order::where('created_at', '>=', $query)->sum('profit');
        $this->data['average_check'] = $this->data['total_orders'] > 0
            ? $this->data['total_revenue'] / $this->data['total_orders']
            : 0;
        $this->data['average_margin'] = $this->data['total_revenue'] > 0
            ? ($this->data['total_profit'] / $this->data['total_revenue']) * 100
            : 0;

        // Статистика по услугам
        $this->data['services_stats'] = Order::select('service_type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total_amount) as revenue')
            ->selectRaw('SUM(profit) as profit')
            ->selectRaw('AVG(profit / total_amount * 100) as margin')
            ->where('created_at', '>=', $query)
            ->groupBy('service_type')
            ->orderByDesc('count')
            ->get();

        // Статистика по инструментам
        $this->data['tools_stats'] = Order::select('tool_type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total_amount) as revenue')
            ->selectRaw('SUM(profit) as profit')
            ->selectRaw('AVG(profit / total_amount * 100) as margin')
            ->where('created_at', '>=', $query)
            ->groupBy('tool_type')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Частота посещений клиентов
        $this->data['client_frequency'] = Client::select('clients.full_name', 'clients.phone')
            ->selectRaw('COUNT(orders.id) as orders_count')
            ->selectRaw('SUM(orders.total_amount) as total_spent')
            ->selectRaw('AVG(orders.total_amount) as avg_order')
            ->leftJoin('orders', 'clients.id', '=', 'orders.client_id')
            ->where('orders.created_at', '>=', $query)
            ->groupBy('clients.id', 'clients.full_name', 'clients.phone')
            ->having('orders_count', '>=', 2)
            ->orderByDesc('orders_count')
            ->limit(20)
            ->get();

        // Статистика по статусам
        $this->data['status_stats'] = Order::select('status')
            ->selectRaw('COUNT(*) as count')
            ->where('created_at', '>=', $query)
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        // Ежемесячная статистика
        $this->data['monthly_stats'] = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('SUM(total_amount) as revenue')
            ->selectRaw('SUM(profit) as profit')
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('period')
                ->label('Период')
                ->options([
                    'week' => 'Неделя',
                    'month' => 'Месяц',
                    'year' => 'Год',
                ])
                ->default('month')
                ->reactive()
                ->afterStateUpdated(fn() => $this->loadData()),
        ];
    }
}
