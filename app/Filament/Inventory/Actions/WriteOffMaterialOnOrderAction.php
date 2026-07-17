<?php

namespace App\Filament\Inventory\Actions;

use App\Application\Inventory\Command\WriteOffMaterialCommand;
use App\Application\Inventory\Command\WriteOffMaterialHandler;
use App\Domain\Order\VO\OrderStatus;
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

final class WriteOffMaterialOnOrderAction
{
    public static function make(): Action
    {
        return Action::make('writeOffMaterial')
            ->label('Списать материал')
            ->icon(Heroicon::OutlinedArchiveBox)
            ->color('gray')
            ->visible(fn (OrderModel $record): bool => in_array($record->status, [
                OrderStatus::WorksCompleted->value,
                OrderStatus::InProgress->value,
            ], true))
            ->form(fn (OrderModel $record): array => [
                Select::make('stock_item_id')
                    ->label('Материал')
                    ->options(fn (): array => StockItemModel::query()
                        ->with('material')
                        ->get()
                        ->mapWithKeys(static function (StockItemModel $item): array {
                            $name = $item->material?->name ?? ('#'.$item->id);
                            $qty = $item->quantity_on_hand;

                            return [(int) $item->id => $name.' (остаток: '.$qty.')'];
                        })
                        ->all())
                    ->searchable()
                    ->required(),
                Select::make('order_item_id')
                    ->label('Позиция (опционально)')
                    ->options(fn (): array => $record->items
                        ->mapWithKeys(static function (OrderItemModel $item): array {
                            $label = $item->tool_name
                                ?: ($item->equipment?->title ?? ('#'.$item->id));

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
            });
    }
}
