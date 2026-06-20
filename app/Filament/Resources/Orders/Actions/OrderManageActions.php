<?php

namespace App\Filament\Resources\Orders\Actions;

use App\Domain\Identity\Enum\UserRole;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Filament\Support\OrderPersistence;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
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
                        ->where('role', UserRole::Master)
                        ->get()
                        ->mapWithKeys(fn (UserModel $user): array => [
                            $user->id => trim($user->name.' '.$user->surname),
                        ])
                        ->all())
                    ->required()
                    ->searchable(),
            ])
            ->action(function (OrderModel $record, array $data): void {
                OrderPersistence::assignMaster($record, (int) $data['master_id']);

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
                $works = $record->works()->get();
                $fields = [];

                foreach ($works as $work) {
                    $fields[] = TextInput::make("prices.{$work->sort_order}")
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
            ->action(function (OrderModel $record, array $data): void {
                if (! isset($data['prices']) || $data['prices'] === []) {
                    return;
                }

                $prices = [];
                foreach ($data['prices'] as $sortOrder => $price) {
                    $prices[(int) $sortOrder] = $price;
                }

                OrderPersistence::setWorkPrices($record, $prices);

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
            ->action(function (OrderModel $record): void {
                $price = OrderPersistence::recalculatePrice($record);

                self::notifySuccess('Итог: '.($price ?? '0').' ₽');
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
            ->action(function (OrderModel $record, array $data): void {
                OrderPersistence::linkEquipment($record, (int) $data['equipment_id']);

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
            ->action(function (OrderModel $record, array $data): void {
                OrderPersistence::addMaterial(
                    $record,
                    (int) $data['warehouse_item_id'],
                    number_format((float) $data['quantity'], 3, '.', ''),
                );

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
            ->action(function (OrderModel $record, array $data): void {
                OrderPersistence::removeMaterial($record, (int) $data['material_id']);

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
            ->action(function (OrderModel $record): void {
                OrderPersistence::issue($record);
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
            ->action(function (OrderModel $record): void {
                OrderPersistence::cancel($record);
                self::notifySuccess('Заказ отменён');
            });
    }

    private static function notifySuccess(string $title): void
    {
        Notification::make()->success()->title($title)->send();
    }
}
