<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PrimaryClientsWidget extends BaseWidget
{
    protected static ?string $heading = 'Клиенты без заказов (первичные)';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Client::query()
                    ->where('is_deleted', false)
                    ->whereDoesntHave('orders')
                    ->orderByDesc('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('ФИО')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->emptyStateHeading('Нет клиентов без заказов')
            ->emptyStateDescription('Все клиенты уже сделали хотя бы один заказ')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}

