<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClientFrequency extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 4;

    protected static ?string $heading = 'Частота посещений';

    public function table(Table $table): Table
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        return $table
            ->query(
                Client::select('clients.full_name', 'clients.phone')
                    ->selectRaw('COUNT(orders.id) as orders_count')
                    ->selectRaw('SUM(orders.total_amount) as total_spent')
                    ->selectRaw('AVG(orders.total_amount) as avg_order')
                    ->leftJoin('orders', 'clients.id', '=', 'orders.client_id')
                    ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                    ->groupBy('clients.id', 'clients.full_name', 'clients.phone')
                    ->having('orders_count', '>=', 2)
                    ->orderByDesc('orders_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->copyable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Заказов')
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 5 => 'danger',
                        $state >= 3 => 'warning',
                        default => 'success',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Потрачено')
                    ->money('RUB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_order')
                    ->label('Средний чек')
                    ->money('RUB')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
