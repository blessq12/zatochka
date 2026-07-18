<?php

namespace App\Filament\Inventory\Actions;

use App\Application\Inventory\Command\SyncOrderMaterialWriteOffItem;
use App\Application\Inventory\Command\SyncOrderMaterialWriteOffsCommand;
use App\Application\Inventory\Command\SyncOrderMaterialWriteOffsHandler;
use App\Application\Inventory\ReadPort\OrderMaterialWriteOffReadPort;
use App\Domain\Order\VO\OrderStatus;
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

final class SyncOrderMaterialWriteOffsAction
{
    public static function make(): Action
    {
        return Action::make('syncOrderMaterialWriteOffs')
            ->label('Редактировать материалы')
            ->icon(Heroicon::OutlinedArchiveBox)
            ->color('gray')
            ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::WorksCompleted->value)
            ->modalHeading('Материалы заказа')
            ->modalDescription('Добавляйте, меняйте количество/цену или удаляйте строки — сохранение применит всё разом.')
            ->fillForm(fn (OrderModel $record): array => [
                'lines' => self::formDefaults((string) $record->id),
            ])
            ->form(fn (OrderModel $record): array => [
                Repeater::make('lines')
                    ->label('')
                    ->schema([
                        Hidden::make('movement_id'),
                        Select::make('stock_item_id')
                            ->label('Материал')
                            ->options(fn (): array => self::stockOptions())
                            ->searchable()
                            ->required()
                            ->live()
                            ->disabled(fn (Get $get): bool => filled($get('movement_id')))
                            ->dehydrated(true)
                            ->afterStateUpdated(function (int|string|null $state, callable $set, Get $get): void {
                                if (filled($get('movement_id')) || $state === null || $state === '') {
                                    return;
                                }

                                $item = StockItemModel::query()->with('material')->find((int) $state);
                                $catalog = $item?->material?->unit_price;
                                if ($catalog !== null && (float) $catalog > 0) {
                                    $set('unit_price', number_format((float) $catalog, 2, '.', ''));
                                }
                            }),
                        Select::make('order_item_id')
                            ->label('Позиция (опционально)')
                            ->options(fn (): array => self::itemOptions($record))
                            ->searchable(),
                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->required()
                            ->minValue(0.001),
                        TextInput::make('unit_price')
                            ->label('Цена за единицу')
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                        TextInput::make('comment')
                            ->label('Комментарий'),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel('Добавить материал')
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(function (array $state): ?string {
                        if (! filled($state['stock_item_id'] ?? null)) {
                            return 'Новый материал';
                        }

                        $item = StockItemModel::query()->with('material')->find((int) $state['stock_item_id']);
                        $name = $item?->material?->name ?? ('#'.$state['stock_item_id']);
                        $qty = $state['quantity'] ?? '—';
                        $price = $state['unit_price'] ?? '—';

                        return $name.' · '.$qty.' × '.$price;
                    }),
            ])
            ->action(function (OrderModel $record, array $data): void {
                try {
                    $lines = [];
                    foreach ($data['lines'] ?? [] as $row) {
                        $lines[] = new SyncOrderMaterialWriteOffItem(
                            stockItemId: (int) $row['stock_item_id'],
                            quantity: (string) $row['quantity'],
                            unitPrice: (string) $row['unit_price'],
                            movementId: isset($row['movement_id']) && $row['movement_id'] !== '' && $row['movement_id'] !== null
                                ? (int) $row['movement_id']
                                : null,
                            orderItemId: isset($row['order_item_id']) && $row['order_item_id'] !== '' && $row['order_item_id'] !== null
                                ? (int) $row['order_item_id']
                                : null,
                            comment: isset($row['comment']) && trim((string) $row['comment']) !== ''
                                ? (string) $row['comment']
                                : null,
                        );
                    }

                    app(SyncOrderMaterialWriteOffsHandler::class)->handle(new SyncOrderMaterialWriteOffsCommand(
                        (string) $record->id,
                        $lines,
                    ));
                    Notification::make()->title('Материалы обновлены')->success()->send();
                } catch (DomainException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();
                }
            });
    }

    /** @return list<array{movement_id: int, stock_item_id: int, quantity: string, unit_price: string, order_item_id: ?int, comment: ?string}> */
    private static function formDefaults(string $orderId): array
    {
        $rows = [];

        foreach (app(OrderMaterialWriteOffReadPort::class)->listActiveByOrderId($orderId) as $line) {
            $rows[] = [
                'movement_id' => $line->movementId,
                'stock_item_id' => $line->stockItemId,
                'quantity' => $line->quantity,
                'unit_price' => $line->unitPrice,
                'order_item_id' => $line->orderItemId,
                'comment' => $line->comment,
            ];
        }

        return $rows;
    }

    /** @return array<int, string> */
    private static function stockOptions(): array
    {
        return StockItemModel::query()
            ->with('material')
            ->get()
            ->mapWithKeys(static function (StockItemModel $item): array {
                $name = $item->material?->name ?? ('#'.$item->id);
                $qty = $item->quantity_on_hand;
                $price = number_format((float) ($item->material?->unit_price ?? 0), 2, '.', '');

                return [(int) $item->id => $name.' (остаток: '.$qty.', цена: '.$price.')'];
            })
            ->all();
    }

    /** @return array<int, string> */
    private static function itemOptions(OrderModel $record): array
    {
        $record->loadMissing('items.equipment');

        return $record->items
            ->mapWithKeys(static function (OrderItemModel $item): array {
                $label = $item->tool_name
                    ?: ($item->equipment?->title ?? ('#'.$item->id));

                return [(int) $item->id => $label];
            })
            ->all();
    }
}
