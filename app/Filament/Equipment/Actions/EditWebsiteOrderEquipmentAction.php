<?php

namespace App\Filament\Equipment\Actions;

use App\Application\Equipment\Command\AddComponentCommand;
use App\Application\Equipment\Command\AddComponentHandler;
use App\Application\Equipment\Command\RegisterSerialNumberCommand;
use App\Application\Equipment\Command\RegisterSerialNumberHandler;
use App\Application\Equipment\Command\UpdateEquipmentCommand;
use App\Application\Equipment\Command\UpdateEquipmentHandler;
use App\Application\Shared\EntityIdGenerator;
use App\Application\Shared\UnitOfWork;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderSource;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Equipment\VO\EquipmentType;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

final class EditWebsiteOrderEquipmentAction
{
    public static function make(): Action
    {
        return Action::make('editWebsiteOrderEquipment')
            ->label('Оборудование')
            ->tooltip('Редактировать оборудование')
            ->icon(Heroicon::OutlinedWrenchScrewdriver)
            ->color('gray')
            ->visible(fn (OrderModel $record): bool => self::isVisible($record))
            ->modalHeading('Редактирование оборудования')
            ->modalDescription('Заявка с сайта: укажите наименование, бренд, модель и добавьте части с серийными номерами.')
            ->modalSubmitActionLabel('Сохранить')
            ->fillForm(fn (OrderModel $record): array => self::formDefaults($record))
            ->form(fn (OrderModel $record): array => self::formSchema($record))
            ->action(function (OrderModel $record, array $data): void {
                try {
                    $equipmentId = self::resolveEquipmentId($record, $data);

                    app(UnitOfWork::class)->execute(function () use ($data, $equipmentId): void {
                        $equipment = ClientEquipmentModel::query()->findOrFail($equipmentId);

                        app(UpdateEquipmentHandler::class)->handle(new UpdateEquipmentCommand(
                            $equipmentId,
                            (string) $data['title'],
                            (string) $data['brand'],
                            (string) $data['model_name'],
                            (string) $data['equipment_type'],
                            $equipment->client_id !== null ? (int) $equipment->client_id : null,
                        ));

                        $ids = app(EntityIdGenerator::class);

                        foreach ($data['existing_components'] ?? [] as $row) {
                            $componentId = (int) ($row['id'] ?? 0);
                            $hadSerial = filled($row['original_serial_number'] ?? null);
                            $serial = trim((string) ($row['serial_number'] ?? ''));

                            if ($componentId <= 0 || $hadSerial || $serial === '') {
                                continue;
                            }

                            app(RegisterSerialNumberHandler::class)->handle(new RegisterSerialNumberCommand(
                                $equipmentId,
                                $componentId,
                                $serial,
                            ));
                        }

                        foreach ($data['new_parts'] ?? [] as $part) {
                            $name = trim((string) ($part['name'] ?? ''));
                            if ($name === '') {
                                continue;
                            }

                            $serial = trim((string) ($part['serialNumber'] ?? ''));

                            app(AddComponentHandler::class)->handle(new AddComponentCommand(
                                $equipmentId,
                                $ids->next('equipment_component')->value,
                                $name,
                                $serial !== '' ? $serial : null,
                            ));
                        }
                    });

                    Notification::make()->title('Оборудование обновлено')->success()->send();
                } catch (DomainException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();
                }
            });
    }

    public static function isVisible(OrderModel $record): bool
    {
        if ((string) $record->source !== OrderSource::Website->value) {
            return false;
        }

        if ((string) $record->service_type !== OrderServiceType::Repair->value) {
            return false;
        }

        if ((string) $record->status !== OrderStatus::Created->value) {
            return false;
        }

        $record->loadMissing('items');

        return $record->items->contains(
            static fn (OrderItemModel $item): bool => $item->client_equipment_id !== null,
        );
    }

    /** @return array<string, mixed> */
    private static function formDefaults(OrderModel $record): array
    {
        $record->loadMissing('items.equipment.components');
        $options = self::equipmentOptions($record);
        $equipmentId = array_key_first($options);
        $equipment = $equipmentId !== null
            ? self::findEquipment($record, (int) $equipmentId)
            : null;

        return [
            'equipment_id' => $equipmentId,
            ...self::equipmentFieldDefaults($equipment),
            'new_parts' => ($equipment === null || $equipment->components->isEmpty())
                ? [['name' => '', 'serialNumber' => '']]
                : [],
        ];
    }

    /** @return list<\Filament\Forms\Components\Component> */
    private static function formSchema(OrderModel $record): array
    {
        $options = self::equipmentOptions($record);
        $single = count($options) === 1;

        $equipmentField = $single
            ? Hidden::make('equipment_id')->required()
            : Select::make('equipment_id')
                ->label('Оборудование')
                ->options($options)
                ->required()
                ->live()
                ->afterStateUpdated(function (mixed $state, Set $set) use ($record): void {
                    $equipment = self::findEquipment($record, (int) $state);
                    $defaults = self::equipmentFieldDefaults($equipment);
                    foreach ($defaults as $key => $value) {
                        $set($key, $value);
                    }
                    $set(
                        'new_parts',
                        ($equipment === null || $equipment->components->isEmpty())
                            ? [['name' => '', 'serialNumber' => '']]
                            : [],
                    );
                });

        return [
            $equipmentField,
            TextInput::make('title')
                ->label('Наименование')
                ->required()
                ->maxLength(255),
            Select::make('equipment_type')
                ->label('Тип оборудования')
                ->options(EquipmentType::options())
                ->required()
                ->native(false)
                ->searchable(),
            TextInput::make('brand')
                ->label('Бренд')
                ->required()
                ->maxLength(255),
            TextInput::make('model_name')
                ->label('Модель')
                ->required()
                ->maxLength(255),
            Repeater::make('existing_components')
                ->label('Текущие части')
                ->schema([
                    Hidden::make('id'),
                    Hidden::make('original_serial_number'),
                    TextInput::make('name')
                        ->label('Название')
                        ->disabled()
                        ->dehydrated(),
                    TextInput::make('serial_number')
                        ->label('Серийный номер')
                        ->disabled(fn (Get $get): bool => filled($get('original_serial_number')))
                        ->dehydrated()
                        ->placeholder('Не указан'),
                ])
                ->defaultItems(0)
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->columns(2)
                ->visible(fn (Get $get): bool => filled($get('existing_components')))
                ->columnSpanFull(),
            Repeater::make('new_parts')
                ->label('Части оборудования')
                ->schema([
                    TextInput::make('name')
                        ->label('Название части')
                        ->required()
                        ->placeholder('Ручка / Блок управления / Блок питания'),
                    TextInput::make('serialNumber')
                        ->label('Серийный номер')
                        ->placeholder('Необязательно'),
                ])
                ->defaultItems(fn (Get $get): int => filled($get('existing_components')) ? 0 : 1)
                ->addActionLabel('Добавить часть')
                ->reorderable(false)
                ->helperText('Добавьте части оборудования и при необходимости серийные номера.')
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function resolveEquipmentId(OrderModel $record, array $data): int
    {
        if (isset($data['equipment_id']) && filled($data['equipment_id'])) {
            return (int) $data['equipment_id'];
        }

        $options = self::equipmentOptions($record);
        $fallback = array_key_first($options);

        if ($fallback === null) {
            throw new DomainException('В заказе нет оборудования для редактирования.');
        }

        return (int) $fallback;
    }

    /** @return array<int, string> */
    private static function equipmentOptions(OrderModel $record): array
    {
        $record->loadMissing('items.equipment');
        $options = [];

        foreach ($record->items as $item) {
            if ($item->client_equipment_id === null || $item->equipment === null) {
                continue;
            }

            $equipment = $item->equipment;
            $label = filled($equipment->title)
                ? (string) $equipment->title
                : (filled($equipment->number) ? (string) $equipment->number : 'Оборудование');
            if (filled($equipment->number) && filled($equipment->title)) {
                $label = (string) $equipment->number.' · '.$label;
            }
            $options[(int) $equipment->id] = $label;
        }

        return $options;
    }

    private static function findEquipment(OrderModel $record, int $equipmentId): ?ClientEquipmentModel
    {
        $record->loadMissing('items.equipment.components');

        foreach ($record->items as $item) {
            if ($item->equipment !== null && (int) $item->equipment->id === $equipmentId) {
                return $item->equipment;
            }
        }

        return ClientEquipmentModel::query()->with('components')->find($equipmentId);
    }

    /**
     * @return array{
     *     title: string,
     *     brand: string,
     *     model_name: string,
     *     existing_components: list<array{id: int, name: string, serial_number: ?string, original_serial_number: ?string}>
     * }
     */
    private static function equipmentFieldDefaults(?ClientEquipmentModel $equipment): array
    {
        if ($equipment === null) {
            return [
                'title' => '',
                'equipment_type' => EquipmentType::Other->value,
                'brand' => '',
                'model_name' => '',
                'existing_components' => [],
            ];
        }

        $equipment->loadMissing('components');

        return [
            'title' => (string) ($equipment->title ?? ''),
            'equipment_type' => (string) ($equipment->equipment_type ?: EquipmentType::Other->value),
            'brand' => (string) ($equipment->brand ?? ''),
            'model_name' => (string) ($equipment->model_name ?? ''),
            'existing_components' => $equipment->components
                ->map(static function ($component): array {
                    $serial = $component->serial_number !== null ? (string) $component->serial_number : null;

                    return [
                        'id' => (int) $component->id,
                        'name' => (string) $component->name,
                        'serial_number' => $serial,
                        'original_serial_number' => $serial,
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}
