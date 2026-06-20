<?php

namespace App\Filament\Resources\SiteLeads\Tables;

use App\Filament\Support\OrderPersistence;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteLeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('full_name')->label('Имя'),
                TextColumn::make('phone')->label('Телефон'),
                TextColumn::make('service_types')
                    ->label('Услуги')
                    ->badge(),
                IconColumn::make('needs_delivery')
                    ->label('Доставка')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Получена')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->recordActions([
                Action::make('createOrder')
                    ->label('Создать заказ')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (SiteLeadModel $record): void {
                        OrderPersistence::createFromFormData([
                            'service_types' => $record->service_types ?? [],
                            'full_name' => $record->full_name,
                            'phone' => $record->phone,
                            'needs_delivery' => $record->needs_delivery,
                            'delivery_address' => $record->delivery_address,
                            'comment' => $record->comment,
                        ], $record);

                        Notification::make()
                            ->success()
                            ->title('Заказ создан из лида')
                            ->send();
                    }),
            ]);
    }
}
