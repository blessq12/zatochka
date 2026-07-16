<?php

namespace App\Filament\Order\Resources\OrderResource\Actions;

use App\Application\Inventory\Command\WriteOffMaterialCommand;
use App\Application\Inventory\Command\WriteOffMaterialHandler;
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
use App\Application\Pricing\Command\SetOrderWorkPricesCommand;
use App\Application\Pricing\Command\SetOrderWorkPricesHandler;
use App\Application\Pricing\ServiceType\WorkPricingPolicyResolver;
use App\Domain\Order\VO\OrderStatus;
use App\Filament\Order\Resources\OrderResource\Support\OrderPresentation;
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Models\User;
use App\Models\UserRole;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

final class OrderMutationActions
{
    /** @return list<Action> */
    public static function all(): array
    {
        return [
            Action::make('assignMaster')
                ->label('Назначить мастера')
                ->icon(Heroicon::OutlinedUserPlus)
                ->color('primary')
                ->visible(fn(OrderModel $record): bool => $record->status === OrderStatus::Created->value
                    && $record->assigned_master_id === null)
                ->form([
                    Select::make('master_id')
                        ->label('Мастер')
                        ->options(fn(): array => User::query()
                            ->where('role', UserRole::Master->value)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (OrderModel $record, array $data): void {
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
            Action::make('setOrderPrices')
                ->label('Назначить цены')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('warning')
                ->visible(fn(OrderModel $record): bool => $record->status === OrderStatus::WorksCompleted->value)
                ->modalHeading('Стоимость выполненных работ')
                ->modalDescription(fn (OrderModel $record): string => app(WorkPricingPolicyResolver::class)
                    ->forValue((string) $record->service_type)
                    ->modalDescription())
                ->fillForm(fn(OrderModel $record): array => [
                    'work_prices' => OrderPresentation::buildWorkPricesFormDefaults($record),
                ])
                ->form(fn(OrderModel $record): array => [
                    Repeater::make('work_prices')
                        ->label('')
                        ->schema([
                            Hidden::make('performed_work_id'),
                            Hidden::make('order_item_id'),
                            TextInput::make('position_label')
                                ->label(app(WorkPricingPolicyResolver::class)
                                    ->forValue((string) $record->service_type)
                                    ->positionFieldLabel())
                                ->disabled()
                                ->dehydrated(false)
                                ->columnSpan(2),
                            TextInput::make('work_description')
                                ->label('Работа')
                                ->disabled()
                                ->dehydrated(false)
                                ->columnSpan(2),
                            TextInput::make('repairable_quantity')
                                ->label('К выдаче')
                                ->disabled()
                                ->dehydrated(false)
                                ->numeric()
                                ->suffix('шт.')
                                ->visible(fn (): bool => app(WorkPricingPolicyResolver::class)
                                    ->forValue((string) $record->service_type)
                                    ->showQuantityColumn())
                                ->columnSpan(1),
                            TextInput::make('base_amount')
                                ->label(app(WorkPricingPolicyResolver::class)
                                    ->forValue((string) $record->service_type)
                                    ->amountFieldLabel())
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->suffix('₽')
                                ->live(onBlur: true)
                                ->columnSpan(1),
                            Placeholder::make('work_line_total')
                                ->label('Итого по работе')
                                ->content(function (Get $get) use ($record): string {
                                    $policy = app(WorkPricingPolicyResolver::class)
                                        ->forValue((string) $record->service_type);
                                    $quantity = $policy->lineQuantity((int) ($get('repairable_quantity') ?? 1));
                                    $unitAmount = (float) ($get('base_amount') ?? 0);

                                    if ($unitAmount <= 0) {
                                        return '—';
                                    }

                                    return OrderPresentation::formatMoney((string) round($unitAmount * $quantity, 2));
                                })
                                ->columnSpan(1),
                        ])
                        ->columns(7)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ])
                ->action(function (OrderModel $record, array $data): void {
                    try {
                        if (OrderPresentation::buildWorkPricesFormDefaults($record) === []) {
                            Notification::make()
                                ->title('Нет выполненных работ для оценки')
                                ->danger()
                                ->send();

                            return;
                        }

                        app(SetOrderWorkPricesHandler::class)->handle(new SetOrderWorkPricesCommand(
                            (string) $record->id,
                            array_map(
                                static fn(array $row): array => [
                                    'performed_work_id' => (int) $row['performed_work_id'],
                                    'base_amount' => (string) $row['base_amount'],
                                ],
                                $data['work_prices'] ?? [],
                            ),
                        ));
                        Notification::make()->title('Стоимость работ сохранена')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('writeOffMaterial')
                ->label('Списать материал')
                ->icon(Heroicon::OutlinedArchiveBox)
                ->color('gray')
                ->visible(fn(OrderModel $record): bool => in_array($record->status, [
                    OrderStatus::WorksCompleted->value,
                    OrderStatus::InProgress->value,
                ], true))
                ->form(fn(OrderModel $record): array => [
                    Select::make('stock_item_id')
                        ->label('Материал')
                        ->options(fn(): array => StockItemModel::query()
                            ->with('material')
                            ->get()
                            ->mapWithKeys(static function (StockItemModel $item): array {
                                $name = $item->material?->name ?? ('#' . $item->id);
                                $qty = $item->quantity_on_hand;

                                return [(int) $item->id => $name . ' (остаток: ' . $qty . ')'];
                            })
                            ->all())
                        ->searchable()
                        ->required(),
                    Select::make('order_item_id')
                        ->label('Позиция (опционально)')
                        ->options(fn(): array => $record->items
                            ->mapWithKeys(static function (OrderItemModel $item): array {
                                $label = $item->tool_name
                                    ?: ($item->equipment?->title ?? ('#' . $item->id));

                                return [(int) $item->id => $label];
                            })
                            ->all())
                        ->searchable(),
                    TextInput::make('quantity')
                        ->label('Количество')
                        ->numeric()
                        ->required()
                        ->minValue(0.001),
                    TextInput::make('comment')
                        ->label('Комментарий'),
                ])
                ->action(function (OrderModel $record, array $data): void {
                    try {
                        app(WriteOffMaterialHandler::class)->handle(new WriteOffMaterialCommand(
                            (int) $data['stock_item_id'],
                            (string) $data['quantity'],
                            $data['comment'] ?? null,
                            (string) $record->id,
                            isset($data['order_item_id']) && $data['order_item_id'] !== '' && $data['order_item_id'] !== null
                                ? (int) $data['order_item_id']
                                : null,
                        ));
                        Notification::make()->title('Материал списан')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('markReady')
                ->label('Готов к выдаче')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->visible(fn(OrderModel $record): bool => $record->status === OrderStatus::WorksCompleted->value)
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
                ->visible(fn(OrderModel $record): bool => $record->status === OrderStatus::WorksCompleted->value)
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
                ->visible(fn(OrderModel $record): bool => $record->status === OrderStatus::Ready->value)
                ->action(function (OrderModel $record): void {
                    try {
                        app(IssueOrderHandler::class)->handle(new IssueOrderCommand((string) $record->id));
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
                ->visible(fn(OrderModel $record): bool => ! in_array($record->status, [
                    OrderStatus::Cancelled->value,
                    OrderStatus::Closed->value,
                    OrderStatus::Issued->value,
                    OrderStatus::Ready->value,
                ], true))
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
}
