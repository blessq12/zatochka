<?php

namespace App\Filament\Resources\Orders\Actions;

use App\Domain\Identity\Enum\UserRole;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\Warehouse\Enum\WarehouseItemType;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Support\OrderManageActionSupport;
use App\Filament\Support\OrderViewPresenter;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                    ->default(fn (OrderModel $record): ?int => $record->master_id)
                    ->required()
                    ->searchable(),
            ])
            ->action(function (OrderModel $record, array $data, ViewOrder $livewire): void {
                OrderManageActionSupport::assignMaster($record->id, (int) $data['master_id']);

                self::complete('Мастер назначен', $livewire);
            });
    }

    public static function setWorkPrices(): Action
    {
        return Action::make('setWorkPrices')
            ->label('Указать цены работ')
            ->icon('heroicon-o-currency-dollar')
            ->visible(fn (OrderModel $record): bool => ! in_array($record->status, [OrderStatus::Issued, OrderStatus::Cancelled], true))
            ->form(function (OrderModel $record): array {
                $record->loadMissing(['tools', 'works']);

                if (OrderViewPresenter::isSharpeningOrder($record) && $record->tools->isNotEmpty()) {
                    return self::sharpeningWorkPriceFields($record);
                }

                return self::repairWorkPriceFields($record);
            })
            ->action(function (OrderModel $record, array $data, ViewOrder $livewire): void {
                try {
                    if (isset($data['prices_by_tool_type']) && is_array($data['prices_by_tool_type'])) {
                        $pricesByToolType = [];

                        foreach ($data['prices_by_tool_type'] as $toolType => $price) {
                            if ($price !== null && $price !== '') {
                                $pricesByToolType[(string) $toolType] = (string) $price;
                            }
                        }

                        if ($pricesByToolType === []) {
                            return;
                        }

                        $order = OrderManageActionSupport::setWorkPrices(
                            orderId: $record->id,
                            pricesByToolType: $pricesByToolType,
                        );
                    } elseif (isset($data['prices']) && is_array($data['prices']) && $data['prices'] !== []) {
                        $prices = [];

                        foreach ($data['prices'] as $sortOrder => $price) {
                            $prices[(int) $sortOrder] = $price;
                        }

                        $order = OrderManageActionSupport::setWorkPrices(
                            orderId: $record->id,
                            pricesBySortOrder: $prices,
                        );
                    } else {
                        return;
                    }

                    self::complete(
                        'Цены сохранены. Итог: '.OrderManageActionSupport::formatPrice($order->price()),
                        $livewire
                    );
                } catch (OrderPolicyViolation $exception) {
                    Notification::make()
                        ->danger()
                        ->title($exception->getMessage())
                        ->send();
                }
            });
    }

    /** @return list<TextInput> */
    private static function sharpeningWorkPriceFields(OrderModel $record): array
    {
        $fields = [];

        foreach (OrderViewPresenter::groupedToolQuantities($record) as $toolType => $quantity) {
            $fields[] = TextInput::make("prices_by_tool_type.{$toolType}")
                ->label(sprintf('%s (цена за ед.)', OrderViewPresenter::toolTypeLabel($toolType)))
                ->numeric()
                ->minValue(0)
                ->default(OrderViewPresenter::toolTypeUnitPrice($record, $toolType))
                ->helperText("× {$quantity} шт. — итог посчитается автоматически");
        }

        if ($fields === []) {
            $fields[] = TextInput::make('empty')
                ->label('Нет инструментов')
                ->disabled()
                ->default('Добавьте инструменты в заказ');
        }

        return $fields;
    }

    /** @return list<TextInput> */
    private static function repairWorkPriceFields(OrderModel $record): array
    {
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
                ->default('Мастер ещё не добавил работы в POS');
        }

        return $fields;
    }

    public static function linkEquipment(): Action
    {
        return Action::make('linkEquipment')
            ->label('Привязать оборудование')
            ->icon('heroicon-o-cpu-chip')
            ->visible(fn (OrderModel $record): bool => in_array('repair', $record->service_types ?? [], true)
                && in_array($record->status, [OrderStatus::InWork, OrderStatus::WaitingParts], true))
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
                    ->default(fn (OrderModel $record): ?int => $record->equipment_id)
                    ->required()
                    ->searchable(),
            ])
            ->action(function (OrderModel $record, array $data, ViewOrder $livewire): void {
                OrderManageActionSupport::linkEquipment($record->id, (int) $data['equipment_id']);

                self::complete('Оборудование привязано', $livewire);
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
            ->icon('heroicon-o-plus')
            ->visible(fn (OrderModel $record): bool => ! in_array($record->status, [OrderStatus::Issued, OrderStatus::Cancelled], true))
            ->form([
                Select::make('warehouse_item_id')
                    ->label('Позиция склада')
                    ->options(function (OrderModel $record): array {
                        $query = WarehouseItemModel::query()->orderBy('name');

                        if (! in_array('repair', $record->service_types ?? [], true)) {
                            $query->where('type', WarehouseItemType::Consumable);
                        }

                        return $query->pluck('name', 'id')->all();
                    })
                    ->required()
                    ->searchable(),
                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->minValue(0.001)
                    ->required()
                    ->default(1),
            ])
            ->action(function (OrderModel $record, array $data, ViewOrder $livewire): void {
                $order = OrderManageActionSupport::addMaterial(
                    $record->id,
                    (int) $data['warehouse_item_id'],
                    number_format((float) $data['quantity'], 3, '.', ''),
                );

                self::complete(
                    'Материал добавлен. Итог: '.OrderManageActionSupport::formatPrice($order->price()),
                    $livewire
                );
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
                            $material->id => sprintf(
                                '%s × %s',
                                OrderViewPresenter::warehouseItemName((int) $material->warehouse_item_id),
                                $material->quantity,
                            ),
                        ])
                        ->all())
                    ->required(),
            ])
            ->action(function (OrderModel $record, array $data, ViewOrder $livewire): void {
                $order = OrderManageActionSupport::removeMaterial(
                    $record->id,
                    (int) $data['material_id']
                );

                self::complete(
                    'Материал удалён. Итог: '.OrderManageActionSupport::formatPrice($order->price()),
                    $livewire
                );
            });
    }

    public static function issue(): Action
    {
        return Action::make('issue')
            ->label('Выдать клиенту')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription('Заказ перейдёт в статус «Выдан». Клиент сможет оставить отзыв.')
            ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::Ready)
            ->action(function (OrderModel $record, ViewOrder $livewire): void {
                OrderManageActionSupport::issue($record->id);

                self::complete('Заказ выдан', $livewire);
            });
    }

    public static function returnForRework(): Action
    {
        return Action::make('returnForRework')
            ->label('Вернуть на доработку')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::Ready)
            ->form([
                Textarea::make('feedback')
                    ->label('Что доработать')
                    ->placeholder('Опишите, что мастер должен исправить или доделать')
                    ->required()
                    ->rows(4)
                    ->maxLength(2000),
            ])
            ->action(function (OrderModel $record, array $data, ViewOrder $livewire): void {
                $managerId = auth()->id();

                if ($managerId === null) {
                    return;
                }

                OrderManageActionSupport::returnForRework(
                    $record->id,
                    (int) $managerId,
                    $data['feedback'],
                );

                self::complete('Заказ возвращён мастеру на доработку', $livewire);
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
            ->action(function (OrderModel $record, ViewOrder $livewire): void {
                OrderManageActionSupport::cancel($record->id);

                self::complete('Заказ отменён', $livewire);
            });
    }

    private static function complete(string $title, ViewOrder $livewire): void
    {
        Notification::make()->success()->title($title)->send();

        $livewire->refreshOrderRecord();
    }
}
