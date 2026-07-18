<?php

namespace App\Filament\Workshop\Actions;

use App\Application\Order\ReadPort\OrderContainerReadPort;
use App\Application\Workshop\Command\SyncOrderPerformedWorkItem;
use App\Application\Workshop\Command\SyncOrderPerformedWorksCommand;
use App\Application\Workshop\Command\SyncOrderPerformedWorksHandler;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

final class SyncOrderPerformedWorksAction
{
    public static function make(): Action
    {
        return Action::make('syncOrderPerformedWorks')
            ->label('Редактировать работы')
            ->icon(Heroicon::OutlinedWrenchScrewdriver)
            ->color('gray')
            ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::WorksCompleted->value)
            ->modalHeading('Выполненные работы')
            ->modalDescription('Добавляйте, правите текст или удаляйте строки — сохранение применит всё разом.')
            ->fillForm(fn (OrderModel $record): array => [
                'works' => self::formDefaults($record),
            ])
            ->form(fn (OrderModel $record): array => [
                Repeater::make('works')
                    ->label('')
                    ->schema(self::workRowSchema($record))
                    ->defaultItems(0)
                    ->addActionLabel('Добавить работу')
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => filled($state['text'] ?? null)
                        ? (string) $state['text']
                        : 'Новая работа'),
            ])
            ->action(function (OrderModel $record, array $data): void {
                try {
                    $items = [];
                    foreach ($data['works'] ?? [] as $row) {
                        $items[] = new SyncOrderPerformedWorkItem(
                            text: (string) ($row['text'] ?? ''),
                            workId: isset($row['work_id']) && $row['work_id'] !== '' && $row['work_id'] !== null
                                ? (int) $row['work_id']
                                : null,
                            orderItemId: isset($row['order_item_id']) && $row['order_item_id'] !== '' && $row['order_item_id'] !== null
                                ? (int) $row['order_item_id']
                                : null,
                            equipmentComponentId: isset($row['equipment_component_id']) && $row['equipment_component_id'] !== '' && $row['equipment_component_id'] !== null
                                ? (int) $row['equipment_component_id']
                                : null,
                        );
                    }

                    app(SyncOrderPerformedWorksHandler::class)->handle(new SyncOrderPerformedWorksCommand(
                        (string) $record->id,
                        $items,
                    ));
                    Notification::make()->title('Работы обновлены')->success()->send();
                } catch (DomainException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();
                }
            });
    }

    /** @return list<\Filament\Forms\Components\Component> */
    private static function workRowSchema(OrderModel $record): array
    {
        $fields = [
            Hidden::make('work_id'),
        ];

        if ($record->service_type === OrderServiceType::Repair->value) {
            $fields[] = Select::make('equipment_component_id')
                ->label('Компонент')
                ->options(fn (): array => self::componentOptions($record))
                ->searchable()
                ->required()
                ->disabled(fn (Get $get): bool => filled($get('work_id')))
                ->dehydrated(true);
        } else {
            $fields[] = Select::make('order_item_id')
                ->label('Позиция')
                ->options(fn (): array => self::sharpeningItemOptions($record))
                ->searchable()
                ->required()
                ->disabled(fn (Get $get): bool => filled($get('work_id')))
                ->dehydrated(true);
        }

        $fields[] = Textarea::make('text')
            ->label('Описание')
            ->required()
            ->rows(2)
            ->maxLength(2000)
            ->columnSpanFull();

        return $fields;
    }

    /** @return list<array{work_id: int, text: string, order_item_id?: int, equipment_component_id?: int}> */
    private static function formDefaults(OrderModel $record): array
    {
        $container = app(OrderContainerReadPort::class)->findById((string) $record->id);
        if ($container === null) {
            return [];
        }

        $rows = [];
        $isRepair = $record->service_type === OrderServiceType::Repair->value;

        foreach ($container->items as $item) {
            foreach ($item->works as $work) {
                $row = [
                    'work_id' => (int) $work['id'],
                    'text' => (string) $work['description'],
                ];

                if ($isRepair) {
                    $row['equipment_component_id'] = isset($work['equipment_component_id'])
                        ? (int) $work['equipment_component_id']
                        : null;
                } else {
                    $row['order_item_id'] = (int) $item->id;
                }

                $rows[] = $row;
            }
        }

        return $rows;
    }

    /** @return array<int, string> */
    private static function sharpeningItemOptions(OrderModel $record): array
    {
        $record->loadMissing('items');

        return $record->items
            ->filter(static fn (OrderItemModel $item): bool => $item->client_equipment_id === null)
            ->mapWithKeys(static function (OrderItemModel $item): array {
                $label = $item->tool_name ?: ('Позиция #'.$item->id);

                return [(int) $item->id => $label];
            })
            ->all();
    }

    /** @return array<int, string> */
    private static function componentOptions(OrderModel $record): array
    {
        $record->loadMissing('items.equipment.components');
        $options = [];

        foreach ($record->items as $item) {
            if ($item->client_equipment_id === null || $item->equipment === null) {
                continue;
            }

            foreach ($item->equipment->components as $component) {
                $options[(int) $component->id] = (string) ($component->name ?: ('Компонент #'.$component->id));
            }
        }

        return $options;
    }
}
