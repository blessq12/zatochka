<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CancelOrderCommand;
use App\Application\OrderFulfillment\Command\IssueOrderCommand;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CancelOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\IssueOrderHandler;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Номер')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
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
                                ->select(['id', 'name', 'surname'])
                                ->get()
                                ->mapWithKeys(fn (UserModel $user): array => [
                                    $user->id => trim($user->name.' '.$user->surname),
                                ])
                                ->all())
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (OrderModel $record, array $data, AssignMasterToOrderHandler $handler): void {
                        $handler->handle(new AssignMasterToOrderCommand(
                            orderId: $record->id,
                            masterId: (int) $data['master_id'],
                        ));

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
                    ->action(function (OrderModel $record, IssueOrderHandler $handler): void {
                        $handler->handle(new IssueOrderCommand($record->id));

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
                    ->action(function (OrderModel $record, CancelOrderHandler $handler): void {
                        $handler->handle(new CancelOrderCommand($record->id));

                        Notification::make()
                            ->success()
                            ->title('Заказ отменён')
                            ->send();
                    }),
            ]);
    }
}
