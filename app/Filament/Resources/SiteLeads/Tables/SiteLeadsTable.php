<?php

namespace App\Filament\Resources\SiteLeads\Tables;

use App\Application\ClientPortal\Support\SiteLeadIntakePresenter;
use App\Filament\Resources\Orders\OrderResource;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use Filament\Actions\Action;
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
                TextColumn::make('intake_data')
                    ->label('Предмет заявки')
                    ->wrap()
                    ->formatStateUsing(fn (SiteLeadModel $record): string => SiteLeadIntakePresenter::summary($record)),
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
                    ->url(fn (SiteLeadModel $record): string => OrderResource::getUrl('create').'?lead='.$record->getKey()),
            ]);
    }
}
