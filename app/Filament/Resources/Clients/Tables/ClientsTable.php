<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('full_name')->label('Имя')->searchable(),
                TextColumn::make('phone')->label('Телефон')->searchable(),
                TextColumn::make('email')->label('Email')->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->dateTime('d.m.Y'),
            ])
            ->recordActions([
                Action::make('linkGuestOrders')
                    ->label('Привязать гостевые заказы')
                    ->icon('heroicon-o-link')
                    ->requiresConfirmation()
                    ->modalDescription('Привяжет заказы без client_id, где телефон в снимке совпадает с телефоном клиента.')
                    ->action(function (ClientModel $record): void {
                        $count = OrderModel::query()
                            ->whereNull('client_id')
                            ->where('client_snapshot->phone', $record->phone)
                            ->update(['client_id' => $record->id]);

                        Notification::make()
                            ->success()
                            ->title("Привязано заказов: {$count}")
                            ->send();
                    }),
            ]);
    }
}
