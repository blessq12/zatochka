<?php

namespace App\Filament\Resources\Orders\Actions;

use App\Application\OrderFulfillment\Command\AddMaterialToOrderCommand;
use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Command\CancelOrderCommand;
use App\Application\OrderFulfillment\Command\IssueOrderCommand;
use App\Application\OrderFulfillment\Command\LinkEquipmentToOrderCommand;
use App\Application\OrderFulfillment\Command\RecalculateOrderPriceCommand;
use App\Application\OrderFulfillment\Command\RemoveMaterialFromOrderCommand;
use App\Application\OrderFulfillment\Command\SetWorkPricesCommand;
use App\Application\OrderFulfillment\CommandHandler\AddMaterialToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\AssignMasterToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\CancelOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\IssueOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\LinkEquipmentToOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\RecalculateOrderPriceHandler;
use App\Application\OrderFulfillment\CommandHandler\RemoveMaterialFromOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\SetWorkPricesHandler;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

final class OrderManageActions
{
    public static function assignMaster(): Action
    {
        return Action::make('assignMaster')
            ->label('Назначить мастера')
            ->icon('heroicon-o-user-plus')
            ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::New)
            ->form([
                Select::make('master_id')
                    ->label('Мастер')
                    ->options(fn (): array => UserModel::query()
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

                self::notifySuccess('Мастер назначен');
            });
    }

    public static function setWorkPrices(): Action
    {
        return Action::make('setWorkPrices')
            ->label('Цены на работы')
            ->icon('heroicon-o-currency-dollar')
            ->visible(fn (OrderModel $record): bool => ! in_array($record->status, [OrderStatus::Issued, OrderStatus::Cancelled], true))
            ->form(function (OrderModel $record): array {
                $order = app(OrderLoader::class)->load($record->id);
                $fields = [];

                foreach ($order->works() as $work) {
                    $fields[] = TextInput::make("prices.{$work->sortOrder}")
                        ->label($work->description)
                        ->numeric()
                        ->minValue(0)
                        ->default($work->price);
                }

                if ($fields === []) {
                    $fields[] = TextInput::make('empty')
                        ->label('Нет работ')
                        ->disabled()
                        ->default('Мастер ещё не добавил работы');
                }

                return $fields;
            })
            ->action(function (OrderModel $record, array $data, SetWorkPricesHandler $handler): void {
                if (! isset($data['prices']) || $data['prices'] === []) {
                    return;
                }

                $prices = [];
                foreach ($data['prices'] as $sortOrder => $price) {
                    $prices[(int) $sortOrder] = $price !== null && $price !== ''
                        ? number_format((float) $price, 2, '.', '')
                        : null;
                }

                $handler->handle(new SetWorkPricesCommand(
                    orderId: $record->id,
                    pricesBySortOrder: $prices,
                ));

                self::notifySuccess('Цены на работы сохранены');
            });
    }

    public static function recalculatePrice(): Action
    {
        return Action::make('recalculatePrice')
            ->label('Пересчитать цену')
            ->icon('heroicon-o-calculator')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (OrderModel $record): bool => ! in_array($record->status, [OrderStatus::Issued, OrderStatus::Cancelled], true))
            ->action(function (OrderModel $record, RecalculateOrderPriceHandler $handler): void {
                $order = $handler->handle(new RecalculateOrderPriceCommand($record->id));

                self::notifySuccess('Итог: '.($order->price() ?? '0').' ₽');
            });
    }

    public static function linkEquipment(): Action
    {
        return Action::make('linkEquipment')
            ->label('Привязать оборудование')
            ->icon('heroicon-o-cpu-chip')
            ->visible(fn (OrderModel $record): bool => ! in_array($record->status, [OrderStatus::Issued, OrderStatus::Cancelled], true))
            ->form([
                Select::make('equipment_id')
                    ->label('Оборудование')
                    ->options(fn (): array => EquipmentModel::query()
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (EquipmentModel $equipment): array => [
                            $equipment->id => trim($equipment->name.' '.($equipment->brand ?? '').' '.($equipment->model ?? '')),
                        ])
                        ->all())
                    ->required()
                    ->searchable(),
            ])
            ->action(function (OrderModel $record, array $data, LinkEquipmentToOrderHandler $handler): void {
                $handler->handle(new LinkEquipmentToOrderCommand(
                    orderId: $record->id,
                    equipmentId: (int) $data['equipment_id'],
                ));

                self::notifySuccess('Оборудование привязано');
            });
    }

    public static function printReceipt(): Action
    {
        return Action::make('printReceipt')
            ->label('Квитанция о приёме')
            ->icon('heroicon-o-document-text')
            ->visible(fn (OrderModel $record): bool => $record->status !== OrderStatus::Cancelled)
            ->url(fn (OrderModel $record): string => route('filament.admin.orders.document', [
                'orderId' => $record->id,
                'type' => 'receipt',
            ]))
            ->openUrlInNewTab();
    }

    public static function printHandoverAct(): Action
    {
        return Action::make('printHandoverAct')
            ->label('Акт выдачи')
            ->icon('heroicon-o-document-check')
            ->visible(fn (OrderModel $record): bool => in_array($record->status, [OrderStatus::Ready, OrderStatus::Issued], true))
            ->url(fn (OrderModel $record): string => route('filament.admin.orders.document', [
                'orderId' => $record->id,
                'type' => 'handover_act',
            ]))
            ->openUrlInNewTab();
    }

    public static function addMaterial(): Action
    {
        return Action::make('addMaterial')
            ->label('Добавить материал')
            ->icon('heroicon-o-cube')
            ->visible(fn (OrderModel $record): bool => ! in_array($record->status, [OrderStatus::Issued, OrderStatus::Cancelled], true))
            ->form([
                Select::make('warehouse_item_id')
                    ->label('Позиция склада')
                    ->options(fn (): array => WarehouseItemModel::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->required()
                    ->searchable(),
                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->minValue(0.001)
                    ->required()
                    ->default(1),
            ])
            ->action(function (OrderModel $record, array $data, AddMaterialToOrderHandler $handler): void {
                $handler->handle(new AddMaterialToOrderCommand(
                    orderId: $record->id,
                    warehouseItemId: (int) $data['warehouse_item_id'],
                    quantity: number_format((float) $data['quantity'], 3, '.', ''),
                ));

                self::notifySuccess('Материал добавлен. Не забудь пересчитать цену.');
            });
    }

    public static function removeMaterial(): Action
    {
        return Action::make('removeMaterial')
            ->label('Удалить материал')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->visible(fn (OrderModel $record): bool => $record->materials()->exists()
                && ! in_array($record->status, [OrderStatus::Issued, OrderStatus::Cancelled], true))
            ->form(fn (OrderModel $record): array => [
                Select::make('material_id')
                    ->label('Материал')
                    ->options(fn (): array => $record->materials()
                        ->get()
                        ->mapWithKeys(fn ($material): array => [
                            $material->id => "ID {$material->warehouse_item_id} × {$material->quantity}",
                        ])
                        ->all())
                    ->required(),
            ])
            ->action(function (OrderModel $record, array $data, RemoveMaterialFromOrderHandler $handler): void {
                $handler->handle(new RemoveMaterialFromOrderCommand(
                    orderId: $record->id,
                    materialId: (int) $data['material_id'],
                ));

                self::notifySuccess('Материал удалён');
            });
    }

    public static function issue(): Action
    {
        return Action::make('issue')
            ->label('Выдать')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::Ready)
            ->action(function (OrderModel $record, IssueOrderHandler $handler): void {
                $handler->handle(new IssueOrderCommand($record->id));
                self::notifySuccess('Заказ выдан');
            });
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label('Отменить')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::New)
            ->action(function (OrderModel $record, CancelOrderHandler $handler): void {
                $handler->handle(new CancelOrderCommand($record->id));
                self::notifySuccess('Заказ отменён');
            });
    }

    private static function notifySuccess(string $title): void
    {
        Notification::make()->success()->title($title)->send();
    }
}
