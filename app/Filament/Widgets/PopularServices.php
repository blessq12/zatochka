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
        return $table
            ->query(Order::latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Услуга')
                    ->formatStateUsing(fn(string $state): string => \App\Models\Order::getServiceTypeOptions()[$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('equipment_type')
                    ->label('Оборудование')
                    ->formatStateUsing(fn(string $state): string => \App\Models\Order::getEquipmentTypeOptions()[$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB'),
            ])
            ->paginated(false);
    }
}
