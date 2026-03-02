<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Заказы';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    protected function getTableQuery(): ?Builder
    {
        return $this->getOwnerRecord()->orders()->withTrashed()->getQuery();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Тип услуги')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::TYPE_REPAIR => 'primary',
                        Order::TYPE_SHARPENING => 'success',
                        Order::TYPE_DIAGNOSTIC => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => Order::getAvailableTypes()[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::STATUS_NEW => 'primary',
                        Order::STATUS_IN_WORK => 'secondary',
                        Order::STATUS_WAITING_PARTS => 'warning',
                        Order::STATUS_READY => 'success',
                        Order::STATUS_ISSUED => 'gray',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => Order::getAvailableStatuses()[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Стоимость')
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('deleted_at')
                    ->label('Удалён')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->trashed())
                    ->trueIcon('heroicon-o-trash')
                    ->falseIcon('heroicon-o-minus')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('deleted_at', $direction);
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Order::getAvailableStatuses()),

                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Тип услуги')
                    ->options(Order::getAvailableTypes()),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->iconButton()
                    ->icon('heroicon-o-eye')
                    ->tooltip('Просмотр')
                    ->url(fn($record) => \App\Filament\Resources\OrderResource::getUrl('view', ['record' => $record])),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
