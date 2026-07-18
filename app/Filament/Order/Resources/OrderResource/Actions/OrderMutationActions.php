<?php

namespace App\Filament\Order\Resources\OrderResource\Actions;

use App\Application\Order\Command\AssignOrderMasterCommand;
use App\Application\Order\Command\AssignOrderMasterHandler;
use App\Application\Order\Command\CancelOrderCommand;
use App\Application\Order\Command\CancelOrderHandler;
use App\Application\Order\Command\CloseOrderCommand;
use App\Application\Order\Command\CloseOrderHandler;
use App\Application\Order\Command\IssueOrderCommand;
use App\Application\Order\Command\IssueOrderHandler;
use App\Application\Order\Command\MarkOrderReadyCommand;
use App\Application\Order\Command\MarkOrderReadyHandler;
use App\Application\Order\Command\ReturnOrderToMasterCommand;
use App\Application\Order\Command\ReturnOrderToMasterHandler;
use App\Domain\Finance\VO\PaymentMethod;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderSource;
use App\Domain\Order\VO\OrderStatus;
use App\Filament\Equipment\Actions\EditWebsiteOrderEquipmentAction;
use App\Filament\Inventory\Actions\SyncOrderMaterialWriteOffsAction;
use App\Filament\Pricing\Actions\SetOrderWorkPricesAction;
use App\Filament\Workshop\Actions\SyncOrderPerformedWorksAction;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Models\User;
use App\Models\UserRole;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

final class OrderMutationActions
{
    /** @return list<Action> */
    public static function all(): array
    {
        return [
            ...self::orderLifecycle(),
            ...self::equipment(),
            ...self::pricing(),
            ...self::inventory(),
        ];
    }

    /** @return list<Action> */
    public static function orderLifecycle(): array
    {
        return [
            Action::make('assignMaster')
                ->label('Назначить мастера')
                ->icon(Heroicon::OutlinedUserPlus)
                ->color('primary')
                ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::Created->value
                    && $record->assigned_master_id === null)
                ->disabled(fn (OrderModel $record): bool => ! self::websiteRepairEquipmentReadyForMaster($record))
                ->tooltip(fn (OrderModel $record): ?string => self::websiteRepairEquipmentReadyForMaster($record)
                    ? null
                    : 'Сначала добавьте к оборудованию хотя бы одну часть с серийным номером')
                ->form([
                    Select::make('master_id')
                        ->label('Мастер')
                        ->options(fn (): array => User::query()
                            ->where('role', UserRole::Master->value)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (OrderModel $record, array $data): void {
                    if (! self::websiteRepairEquipmentReadyForMaster($record)) {
                        Notification::make()
                            ->title('Сначала добавьте к оборудованию хотя бы одну часть с серийным номером')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        app(AssignOrderMasterHandler::class)->handle(new AssignOrderMasterCommand(
                            (string) $record->id,
                            (int) $data['master_id'],
                        ));
                        Notification::make()->title('Мастер назначен')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('markReady')
                ->label('Готов к выдаче')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::WorksCompleted->value)
                ->requiresConfirmation()
                ->action(function (OrderModel $record): void {
                    try {
                        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand((string) $record->id));
                        Notification::make()->title('Заказ готов к выдаче')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('returnToMaster')
                ->label('Вернуть мастеру')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('danger')
                ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::WorksCompleted->value)
                ->modalHeading('Вернуть заказ мастеру на доработку')
                ->form([
                    TextInput::make('reason')
                        ->label('Комментарий для мастера')
                        ->required()
                        ->maxLength(2000),
                ])
                ->action(function (OrderModel $record, array $data): void {
                    try {
                        app(ReturnOrderToMasterHandler::class)->handle(new ReturnOrderToMasterCommand(
                            (string) $record->id,
                            (string) $data['reason'],
                        ));
                        Notification::make()->title('Заказ возвращён мастеру')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('issue')
                ->label('Выдать')
                ->icon(Heroicon::OutlinedHandRaised)
                ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::Ready->value)
                ->modalHeading('Выдача заказа')
                ->form(fn (OrderModel $record): array => $record->billing_type === OrderBillingType::Warranty->value
                    ? []
                    : [
                        Select::make('payment_method')
                            ->label('Метод оплаты')
                            ->options(PaymentMethod::options())
                            ->required(),
                    ])
                ->action(function (OrderModel $record, array $data): void {
                    try {
                        $method = $record->billing_type === OrderBillingType::Warranty->value
                            ? null
                            : (string) ($data['payment_method'] ?? '');

                        app(IssueOrderHandler::class)->handle(new IssueOrderCommand(
                            (string) $record->id,
                            $method,
                        ));
                        Notification::make()->title('Заказ выдан')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('close')
                ->label('Закрыть')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->visible(false)
                ->action(function (OrderModel $record): void {
                    try {
                        app(CloseOrderHandler::class)->handle(new CloseOrderCommand((string) $record->id));
                        Notification::make()->title('Заказ закрыт')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('cancel')
                ->label('Отменить')
                ->icon(Heroicon::OutlinedXMark)
                ->color('danger')
                ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::Created->value)
                ->requiresConfirmation()
                ->form([
                    TextInput::make('reason')
                        ->label('Причина')
                        ->required(),
                ])
                ->action(function (OrderModel $record, array $data): void {
                    try {
                        app(CancelOrderHandler::class)->handle(new CancelOrderCommand(
                            (string) $record->id,
                            $data['reason'],
                        ));
                        Notification::make()->title('Заказ отменён')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    /** @return list<Action> */
    public static function equipment(): array
    {
        return [
            EditWebsiteOrderEquipmentAction::make(),
        ];
    }

    /** @return list<Action> */
    public static function pricing(): array
    {
        return [
            SyncOrderPerformedWorksAction::make(),
            SetOrderWorkPricesAction::make()
                ->label('Цены работ'),
        ];
    }

    /** @return list<Action> */
    public static function inventory(): array
    {
        return [
            SyncOrderMaterialWriteOffsAction::make(),
        ];
    }

    /**
     * Для заказов ремонта с сайта мастер назначается только после
     * дополнения оборудования: хотя бы одна часть с серийным номером.
     */
    public static function websiteRepairEquipmentReadyForMaster(OrderModel $record): bool
    {
        if ((string) $record->source !== OrderSource::Website->value) {
            return true;
        }

        if ((string) $record->service_type !== OrderServiceType::Repair->value) {
            return true;
        }

        $record->loadMissing('items.equipment.components');

        $equipmentItems = $record->items->filter(
            static fn (OrderItemModel $item): bool => $item->client_equipment_id !== null,
        );

        if ($equipmentItems->isEmpty()) {
            return false;
        }

        foreach ($equipmentItems as $item) {
            $hasSerializedPart = $item->equipment?->components->contains(
                static fn ($component): bool => trim((string) ($component->serial_number ?? '')) !== '',
            ) ?? false;

            if (! $hasSerializedPart) {
                return false;
            }
        }

        return true;
    }
}
