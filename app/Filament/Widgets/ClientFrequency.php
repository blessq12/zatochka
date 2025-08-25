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
        return $table
            ->query(Client::withCount('orders')->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Клиент'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон'),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Заказов')
                    ->badge(),
            ])
            ->paginated(false);
    }
}
