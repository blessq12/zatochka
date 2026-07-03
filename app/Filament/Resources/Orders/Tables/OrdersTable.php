<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Domain\Identity\Enum\UserRole;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Filament\Support\OrderManageActionSupport;
use App\Filament\Support\OrderTableSearch;
use App\Filament\Support\OrderViewPresenter;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->searchUsing(fn (Builder $query, string $search): Builder => OrderTableSearch::apply($query, $search))
            ->columns([
                TextColumn::make('order_number')
                    ->label('Номер'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (OrderStatus $state): string => OrderViewPresenter::statusColor($state))
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label()),
                TextColumn::make('client_snapshot.full_name')
                    ->label('Клиент')
                    ->placeholder('—'),
                TextColumn::make('master_id')
                    ->label('Мастер')
                    ->formatStateUsing(function (?int $state): string {
                        if ($state === null) {
                            return '—';
                        }

                        $user = UserModel::query()->find($state);

                        return $user ? trim($user->name.' '.$user->surname) : '—';
                    }),
                TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Создан')
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
                SelectFilter::make('master_id')
                    ->label('Мастер')
                    ->options(fn (): array => UserModel::query()
                        ->where('role', UserRole::Master)
                        ->orderBy('name')
                        ->get(['id', 'name', 'surname'])
                        ->mapWithKeys(fn (UserModel $user): array => [
                            $user->id => trim($user->name.' '.$user->surname),
                        ])
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('assignMaster')
                    ->label('Назначить мастера')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::New)
                    ->form([
                        Select::make('master_id')
                            ->label('Мастер')
                            ->options(fn (): array => UserModel::query()
                                ->where('role', UserRole::Master)
                                ->select(['id', 'name', 'surname'])
                                ->get()
                                ->mapWithKeys(fn (UserModel $user): array => [
                                    $user->id => trim($user->name.' '.$user->surname),
                                ])
                                ->all())
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (OrderModel $record, array $data): void {
                        OrderManageActionSupport::assignMaster($record->id, (int) $data['master_id']);

                        Notification::make()
                            ->success()
                            ->title('Мастер назначен')
                            ->send();
                    }),
                Action::make('issue')
                    ->label('Выдать')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::Ready)
                    ->action(function (OrderModel $record): void {
                        OrderManageActionSupport::issue($record->id);

                        Notification::make()
                            ->success()
                            ->title('Заказ выдан')
                            ->send();
                    }),
                Action::make('cancel')
                    ->label('Отменить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::New)
                    ->action(function (OrderModel $record): void {
                        OrderManageActionSupport::cancel($record->id);

                        Notification::make()
                            ->success()
                            ->title('Заказ отменён')
                            ->send();
                    }),
            ]);
    }
}
