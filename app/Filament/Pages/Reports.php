<?php

namespace App\Filament\Pages;

use App\Models\Order;
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

        // Популярные инструменты
        $this->data['popular_tools'] = OrderTool::select('tools.name', DB::raw('COUNT(*) as count'))
            ->join('tools', 'order_tools.tool_id', '=', 'tools.id')
            ->where('order_tools.created_at', '>=', $query)
            ->groupBy('tools.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Популярные виды ремонта
        $this->data['popular_repairs'] = Repair::select('description', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $query)
            ->groupBy('description')
            ->orderByDesc('count')
            ->limit(5)
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
