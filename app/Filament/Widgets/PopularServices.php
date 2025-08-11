<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PopularServices extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 3;

    protected static ?string $heading = 'Популярные услуги';

    public function table(Table $table): Table
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        return $table
            ->query(
                Order::select('service_type', 'tool_type')
                    ->selectRaw('COUNT(*) as total_orders')
                    ->selectRaw('SUM(total_amount) as total_revenue')
                    ->selectRaw('SUM(profit) as total_profit')
                    ->selectRaw('AVG(profit / total_amount * 100) as avg_margin')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->groupBy('service_type', 'tool_type')
                    ->orderByDesc('total_orders')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Услуга')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'repair' => 'Ремонт',
                        'maintenance' => 'Заточка',
                        'consultation' => 'Консультация',
                        'other' => 'Другое',
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'repair' => 'danger',
                        'maintenance' => 'warning',
                        'consultation' => 'info',
                        'other' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tool_type')
                    ->label('Инструмент')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'drill' => 'Дрель',
                        'screwdriver' => 'Шуруповерт',
                        'grinder' => 'Болгарка',
                        'saw' => 'Пила',
                        'hammer' => 'Перфоратор',
                        'jigsaw' => 'Лобзик',
                        'planer' => 'Рубанок',
                        'other' => 'Другое',
                    }),
                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Заказов')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Выручка')
                    ->money('RUB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_margin')
                    ->label('Маржинальность')
                    ->formatStateUsing(fn($state): string => number_format($state, 1) . '%')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
