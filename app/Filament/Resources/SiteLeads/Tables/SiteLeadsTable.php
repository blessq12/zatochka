<?php

namespace App\Filament\Resources\SiteLeads\Tables;

use App\Application\ClientPortal\Support\SiteLeadIntakePresenter;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\OrderViewPresenter;
use App\Filament\Support\SiteLeadTableSearch;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SiteLeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->searchUsing(fn (Builder $query, string $search): Builder => SiteLeadTableSearch::apply($query, $search))
            ->columns([
                TextColumn::make('full_name')
                    ->label('Имя'),
                TextColumn::make('phone')
                    ->label('Телефон'),
                TextColumn::make('service_types')
                    ->label('Услуги')
                    ->badge()
                    ->state(fn (SiteLeadModel $record): array => OrderViewPresenter::serviceTypeLabels($record->service_types)),
                TextColumn::make('intake_subject')
                    ->label('Предмет заявки')
                    ->state(fn (SiteLeadModel $record): string => SiteLeadIntakePresenter::summary($record))
                    ->limit(50)
                    ->tooltip(fn (SiteLeadModel $record): string => SiteLeadIntakePresenter::summary($record)),
                IconColumn::make('needs_delivery')
                    ->label('Доставка')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Получена')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->filters([
                SelectFilter::make('service_type')
                    ->label('Услуга')
                    ->options([
                        'sharpening' => 'Заточка',
                        'repair' => 'Ремонт',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        return $query->whereJsonContains('service_types', $value);
                    }),
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
