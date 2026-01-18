<?php

namespace App\Filament\Widgets;

use App\Models\Master;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class MastersPerformanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Производительность мастеров';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        return $table
            ->query(
                Master::query()
                    ->where('is_deleted', false)
                    ->withCount([
                        'orders as completed_this_month' => function ($query) use ($monthStart) {
                            $query->where('is_deleted', false)
                                ->where('status', Order::STATUS_ISSUED)
                                ->where('updated_at', '>=', $monthStart);
                        },
                        'orders as in_work_count' => function ($query) {
                            $query->where('is_deleted', false)
                                ->where('status', Order::STATUS_IN_WORK);
                        },
                    ])
                    ->withSum([
                        'orders as revenue_this_month' => function ($query) use ($monthStart) {
                            $query->where('is_deleted', false)
                                ->where('status', Order::STATUS_ISSUED)
                                ->where('updated_at', '>=', $monthStart);
                        }
                    ], 'actual_price')
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Мастер')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('completed_this_month')
                    ->label('Завершено за месяц')
                    ->sortable()
                    ->alignCenter()
                    ->color('success'),

                Tables\Columns\TextColumn::make('in_work_count')
                    ->label('В работе')
                    ->sortable()
                    ->alignCenter()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('revenue_this_month')
                    ->label('Выручка за месяц')
                    ->money('RUB')
                    ->sortable()
                    ->alignRight()
                    ->default(0),
            ])
            ->defaultSort('completed_this_month', 'desc')
            ->emptyStateHeading('Нет данных о мастерах');
    }
}
