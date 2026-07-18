<?php

namespace App\Filament\Order\Resources\OrderResource\Pages;

use App\Application\CRM\ReadPort\ClientReadPort;
use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Filament\CRM\Support\RegisterClientOption;
use App\Filament\Equipment\Support\RegisterEquipmentOption;
use App\Filament\Order\Resources\OrderResource;
use App\Filament\Order\Resources\OrderResource\Support\WarrantySourceOrderSelect;
use App\Filament\Support\ClientSelectField;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Новый заказ';

    public function getWizardComponent(): Component
    {
        return Wizard::make($this->getSteps())
            ->startOnStep($this->getStartStep())
            ->cancelAction($this->getCancelFormAction())
            ->submitAction($this->getSubmitFormAction())
            ->alpineSubmitHandler("\$wire.{$this->getSubmitFormLivewireMethodName()}()")
            ->skippable(false)
            ->contained(false)
            ->persistStepInQueryString('step')
            ->nextAction(fn (Action $action): Action => $action->label('Далее')->icon(Heroicon::OutlinedArrowRight))
            ->previousAction(fn (Action $action): Action => $action->label('Назад')->icon(Heroicon::OutlinedArrowLeft));
    }

    protected function getSteps(): array
    {
        return [
            $this->parametersStep(),
            $this->clientStep(),
            $this->compositionStep(),
            $this->receptionStep(),
        ];
    }

    private function parametersStep(): Step
    {
        return Step::make('Параметры')
            ->description('Тип, вид и срочность')
            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            ->schema([
                Section::make('Параметры заказа')
                    ->description('Базовая классификация приёмки')
                    ->schema([
                        ToggleButtons::make('service_type')
                            ->label('Тип заказа')
                            ->options([
                                OrderServiceType::Sharpening->value => 'Заточка',
                                OrderServiceType::Repair->value => 'Ремонт',
                            ])
                            ->icons([
                                OrderServiceType::Sharpening->value => Heroicon::OutlinedScissors,
                                OrderServiceType::Repair->value => Heroicon::OutlinedWrenchScrewdriver,
                            ])
                            ->colors([
                                OrderServiceType::Sharpening->value => 'info',
                                OrderServiceType::Repair->value => 'warning',
                            ])
                            ->grouped()
                            ->required()
                            ->live(),
                        ToggleButtons::make('billing_type')
                            ->label('Вид заказа')
                            ->options([
                                OrderBillingType::Paid->value => 'Платный',
                                OrderBillingType::Warranty->value => 'Гарантийный',
                            ])
                            ->icons([
                                OrderBillingType::Paid->value => Heroicon::OutlinedBanknotes,
                                OrderBillingType::Warranty->value => Heroicon::OutlinedShieldCheck,
                            ])
                            ->colors([
                                OrderBillingType::Paid->value => 'success',
                                OrderBillingType::Warranty->value => 'danger',
                            ])
                            ->grouped()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                if ($state !== OrderBillingType::Warranty->value) {
                                    $set('warranty_source_order_id', null);
                                }
                            }),
                        ToggleButtons::make('urgency')
                            ->label('Срочность')
                            ->options([
                                OrderUrgency::Normal->value => 'Обычный',
                                OrderUrgency::Urgent->value => 'Срочный',
                            ])
                            ->icons([
                                OrderUrgency::Normal->value => Heroicon::OutlinedClock,
                                OrderUrgency::Urgent->value => Heroicon::OutlinedBolt,
                            ])
                            ->colors([
                                OrderUrgency::Normal->value => 'gray',
                                OrderUrgency::Urgent->value => 'danger',
                            ])
                            ->grouped()
                            ->required(),
                    ]),
                Section::make('Гарантийный заказ')
                    ->description('Выберите исходный заказ, по которому оформляется гарантия')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->visible(fn (Get $get): bool => $get('billing_type') === OrderBillingType::Warranty->value)
                    ->schema([
                        WarrantySourceOrderSelect::make()
                            ->required(fn (Get $get): bool => $get('billing_type') === OrderBillingType::Warranty->value)
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                if (blank($state)) {
                                    return;
                                }

                                $order = OrderModel::query()->find((int) $state);

                                if ($order === null) {
                                    return;
                                }

                                $set('client_mode', 'existing');
                                $set('client_id', (int) $order->client_id);
                                $set('client_picker', (int) $order->client_id);
                                $set('client_equipment_ids', []);
                                $set('equipment_picker', []);
                                $set('equipment_mode', 'existing');
                                $this->clearNewClientFields($set);
                                $this->clearNewEquipmentFields($set);
                            }),
                    ]),
            ]);
    }

    private function clientStep(): Step
    {
        $warrantyLocksClient = fn (Get $get): bool => $get('billing_type') === OrderBillingType::Warranty->value
            && filled($get('warranty_source_order_id'));

        return Step::make('Клиент')
            ->description('Выбрать существующего или создать')
            ->icon(Heroicon::OutlinedUser)
            ->afterValidation(function (): void {
                if (($this->data['client_mode'] ?? null) !== 'new') {
                    return;
                }

                if (filled($this->data['client_id'] ?? null)) {
                    return;
                }

                $this->data['client_id'] = $this->registerOrReuseClient([
                    'name' => $this->data['new_client_name'] ?? null,
                    'phone' => $this->data['new_client_phone'] ?? null,
                    'email' => $this->data['new_client_email'] ?? null,
                ]);
            })
            ->schema([
                Section::make('Клиент')
                    ->description('Выберите: указать существующего клиента или создать нового')
                    ->schema([
                        ToggleButtons::make('client_mode')
                            ->label('Клиент')
                            ->options([
                                'existing' => 'Выбрать существующего',
                                'new' => 'Создать',
                            ])
                            ->icons([
                                'existing' => Heroicon::OutlinedMagnifyingGlass,
                                'new' => Heroicon::OutlinedUserPlus,
                            ])
                            ->colors([
                                'existing' => 'info',
                                'new' => 'success',
                            ])
                            ->grouped()
                            ->default('existing')
                            ->required()
                            ->live()
                            ->disabled($warrantyLocksClient)
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                if ($state === 'existing') {
                                    $this->clearNewClientFields($set);

                                    return;
                                }

                                $set('client_id', null);
                                $set('client_picker', null);
                                $set('client_equipment_ids', []);
                                $set('equipment_picker', []);
                            }),
                        Hidden::make('client_id')->dehydrated(true),
                        ClientSelectField::make('client_picker')
                            ->label('Клиент из базы')
                            ->visible(fn (Get $get): bool => ($get('client_mode') ?? 'existing') === 'existing')
                            ->required(fn (Get $get): bool => ($get('client_mode') ?? 'existing') === 'existing')
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                $set('client_id', $state);
                                $set('client_equipment_ids', []);
                                $set('equipment_picker', []);
                            })
                            ->disabled($warrantyLocksClient),
                        TextInput::make('new_client_name')
                            ->label('ФИО')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => $get('client_mode') === 'new')
                            ->visible(fn (Get $get): bool => $get('client_mode') === 'new')
                            ->dehydrated(fn (Get $get): bool => $get('client_mode') === 'new'),
                        TextInput::make('new_client_phone')
                            ->label('Телефон')
                            ->tel()
                            ->telRegex('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/')
                            ->mask('+7 (999) 999-99-99')
                            ->placeholder('+7 (___) ___-__-__')
                            ->required(fn (Get $get): bool => $get('client_mode') === 'new')
                            ->visible(fn (Get $get): bool => $get('client_mode') === 'new')
                            ->dehydrated(fn (Get $get): bool => $get('client_mode') === 'new'),
                        TextInput::make('new_client_email')
                            ->label('Эл. почта')
                            ->email()
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('client_mode') === 'new')
                            ->dehydrated(fn (Get $get): bool => $get('client_mode') === 'new'),
                    ]),
            ]);
    }

    private function compositionStep(): Step
    {
        return Step::make('Состав')
            ->description('Инструменты или оборудование')
            ->icon(Heroicon::OutlinedCube)
            ->afterValidation(function (): void {
                if (($this->data['service_type'] ?? null) !== OrderServiceType::Repair->value) {
                    return;
                }

                if (($this->data['equipment_mode'] ?? null) !== 'new') {
                    return;
                }

                if (filled($this->data['client_equipment_ids'] ?? null)) {
                    return;
                }

                $this->data['client_equipment_ids'] = [RegisterEquipmentOption::register([
                    'title' => $this->data['new_equipment_title'] ?? null,
                    'brand' => $this->data['new_equipment_brand'] ?? null,
                    'model_name' => $this->data['new_equipment_model_name'] ?? null,
                    'notes' => $this->data['new_equipment_notes'] ?? null,
                    'parts' => $this->data['new_equipment_parts'] ?? [],
                ], (int) ($this->data['client_id'] ?? 0))];
            })
            ->schema([
                Section::make('Инструменты')
                    ->description('Укажите каждый инструмент отдельно')
                    ->icon(Heroicon::OutlinedScissors)
                    ->visible(fn (Get $get): bool => $get('service_type') === OrderServiceType::Sharpening->value)
                    ->schema([
                        Repeater::make('tools')
                            ->label('Список инструментов')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Наименование')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                Select::make('tool_type')
                                    ->label('Тип инструмента')
                                    ->options(SharpeningToolType::options())
                                    ->searchable()
                                    ->required()
                                    ->native(false),
                                TextInput::make('quantity')
                                    ->label('Количество')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('Добавить инструмент')
                            ->cloneable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null)
                                ? (string) $state['name']
                                : 'Инструмент')
                            ->required(),
                    ]),
                Section::make('Оборудование')
                    ->description('Выберите: указать существующее оборудование или создать новое')
                    ->icon(Heroicon::OutlinedWrenchScrewdriver)
                    ->visible(fn (Get $get): bool => $get('service_type') === OrderServiceType::Repair->value)
                    ->schema([
                        ToggleButtons::make('equipment_mode')
                            ->label('Оборудование')
                            ->options([
                                'existing' => 'Выбрать существующее',
                                'new' => 'Создать',
                            ])
                            ->icons([
                                'existing' => Heroicon::OutlinedMagnifyingGlass,
                                'new' => Heroicon::OutlinedPlusCircle,
                            ])
                            ->colors([
                                'existing' => 'info',
                                'new' => 'success',
                            ])
                            ->grouped()
                            ->default('existing')
                            ->required()
                            ->live()
                            ->disabled(fn (Get $get): bool => blank($get('client_id')))
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                if ($state === 'existing') {
                                    $this->clearNewEquipmentFields($set);

                                    return;
                                }

                                $set('client_equipment_ids', []);
                                $set('equipment_picker', []);
                            }),
                        Hidden::make('client_equipment_ids')->dehydrated(true),
                        $this->clientEquipmentSelect()
                            ->visible(fn (Get $get): bool => ($get('equipment_mode') ?? 'existing') === 'existing')
                            ->required(fn (Get $get): bool => ($get('equipment_mode') ?? 'existing') === 'existing'
                                && $get('service_type') === OrderServiceType::Repair->value)
                            ->dehydrated(false)
                            ->disabled(fn (Get $get): bool => blank($get('client_id'))),
                        TextInput::make('new_equipment_title')
                            ->label('Название')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => $get('equipment_mode') === 'new')
                            ->visible(fn (Get $get): bool => $get('equipment_mode') === 'new')
                            ->dehydrated(fn (Get $get): bool => $get('equipment_mode') === 'new'),
                        TextInput::make('new_equipment_brand')
                            ->label('Бренд')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => $get('equipment_mode') === 'new')
                            ->visible(fn (Get $get): bool => $get('equipment_mode') === 'new')
                            ->dehydrated(fn (Get $get): bool => $get('equipment_mode') === 'new'),
                        TextInput::make('new_equipment_model_name')
                            ->label('Модель')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => $get('equipment_mode') === 'new')
                            ->visible(fn (Get $get): bool => $get('equipment_mode') === 'new')
                            ->dehydrated(fn (Get $get): bool => $get('equipment_mode') === 'new'),
                        Textarea::make('new_equipment_notes')
                            ->label('Заметки')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => $get('equipment_mode') === 'new')
                            ->dehydrated(fn (Get $get): bool => $get('equipment_mode') === 'new'),
                        Repeater::make('new_equipment_parts')
                            ->label('Части оборудования')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Название')
                                    ->required()
                                    ->placeholder('Ручка / Блок управления / Блок питания'),
                                TextInput::make('serialNumber')
                                    ->label('Серийный номер')
                                    ->placeholder('Необязательно'),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Добавить часть')
                            ->columns(2)
                            ->columnSpanFull()
                            ->collapsible()
                            ->visible(fn (Get $get): bool => $get('equipment_mode') === 'new')
                            ->dehydrated(fn (Get $get): bool => $get('equipment_mode') === 'new'),
                    ]),
            ]);
    }

    private function clientEquipmentSelect(): Select
    {
        return Select::make('equipment_picker')
            ->label('Оборудование клиента')
            ->multiple()
            ->searchable()
            ->preload()
            ->helperText('Выберите одно или несколько единиц из базы клиента')
            ->live()
            ->afterStateUpdated(fn (mixed $state, Set $set) => $set('client_equipment_ids', $state ?? []))
            ->options(function (Get $get): array {
                if (blank($get('client_id'))) {
                    return [];
                }

                return ClientEquipmentModel::query()
                    ->where('client_id', (int) $get('client_id'))
                    ->orderBy('title')
                    ->get()
                    ->mapWithKeys(static function (ClientEquipmentModel $equipment): array {
                        return [(int) $equipment->id => self::equipmentLabel($equipment)];
                    })
                    ->all();
            })
            ->getOptionLabelsUsing(function (array $values): array {
                return ClientEquipmentModel::query()
                    ->whereIn('id', $values)
                    ->get()
                    ->mapWithKeys(static fn (ClientEquipmentModel $equipment): array => [
                        (int) $equipment->id => self::equipmentLabel($equipment),
                    ])
                    ->all();
            });
    }

    private static function equipmentLabel(ClientEquipmentModel $equipment): string
    {
        return trim($equipment->title.' · '.$equipment->brand.' '.$equipment->model_name);
    }

    private function receptionStep(): Step
    {
        return Step::make('Приёмка')
            ->description('Стоимость, доставка и примечания')
            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
            ->schema([
                Section::make('Стоимость и доставка')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('estimated_amount')
                                ->label('Ориентировочная стоимость')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->prefix('₽'),
                            Toggle::make('delivery_required')
                                ->label('Нужна доставка')
                                ->inline(false)
                                ->onIcon(Heroicon::OutlinedTruck)
                                ->offIcon(Heroicon::OutlinedXMark)
                                ->default(false),
                        ]),
                    ]),
                Section::make('Заметки приёмки')
                    ->schema([
                        Textarea::make('defects')
                            ->label('Дефекты')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('internal_notes')
                            ->label('Внутренние заметки')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Не видны клиенту'),
                    ]),
            ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $data = $this->resolveClientIdForCreate($data);
            $data = $this->resolveEquipmentIdsForCreate($data);

            $orderId = OrderId::generate()->value;
            $items = $this->buildItems($data);
            $isWarranty = ($data['billing_type'] ?? null) === OrderBillingType::Warranty->value;

            app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
                $orderId,
                (int) ($data['client_id'] ?? 0),
                (string) $data['estimated_amount'],
                $items,
                (string) $data['service_type'],
                (string) $data['billing_type'],
                (string) $data['urgency'],
                (bool) ($data['delivery_required'] ?? false),
                $data['defects'] ?? null,
                $data['internal_notes'] ?? null,
                'RUB',
                $isWarranty ? (string) $data['warranty_source_order_id'] : null,
            ));

            return OrderModel::query()->findOrFail($orderId);
        } catch (DomainException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw ValidationException::withMessages([
                'data.service_type' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Notification::make()->title('Не удалось создать заказ')->danger()->send();

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveClientIdForCreate(array $data): array
    {
        if (filled($data['client_id'] ?? null)) {
            return $data;
        }

        if (filled($this->data['client_id'] ?? null)) {
            $data['client_id'] = $this->data['client_id'];

            return $data;
        }

        if (($data['client_mode'] ?? $this->data['client_mode'] ?? null) !== 'new') {
            return $data;
        }

        $payload = [
            'name' => $data['new_client_name'] ?? $this->data['new_client_name'] ?? null,
            'phone' => $data['new_client_phone'] ?? $this->data['new_client_phone'] ?? null,
            'email' => $data['new_client_email'] ?? $this->data['new_client_email'] ?? null,
        ];

        $data['client_id'] = $this->registerOrReuseClient($payload);
        $this->data['client_id'] = $data['client_id'];

        return $data;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function registerOrReuseClient(array $payload): int
    {
        $phone = isset($payload['phone']) ? (string) $payload['phone'] : '';

        if ($phone !== '') {
            $existing = app(ClientReadPort::class)->findByPhone($phone);

            if ($existing !== null) {
                return $existing->id;
            }
        }

        return RegisterClientOption::register($payload);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveEquipmentIdsForCreate(array $data): array
    {
        if (($data['service_type'] ?? null) !== OrderServiceType::Repair->value) {
            return $data;
        }

        if (filled($data['client_equipment_ids'] ?? null)) {
            return $data;
        }

        if (filled($this->data['client_equipment_ids'] ?? null)) {
            $data['client_equipment_ids'] = $this->data['client_equipment_ids'];

            return $data;
        }

        if (($data['equipment_mode'] ?? $this->data['equipment_mode'] ?? null) !== 'new') {
            return $data;
        }

        $equipmentId = RegisterEquipmentOption::register([
            'title' => $data['new_equipment_title'] ?? $this->data['new_equipment_title'] ?? null,
            'brand' => $data['new_equipment_brand'] ?? $this->data['new_equipment_brand'] ?? null,
            'model_name' => $data['new_equipment_model_name'] ?? $this->data['new_equipment_model_name'] ?? null,
            'notes' => $data['new_equipment_notes'] ?? $this->data['new_equipment_notes'] ?? null,
            'parts' => $data['new_equipment_parts'] ?? $this->data['new_equipment_parts'] ?? [],
        ], (int) ($data['client_id'] ?? 0));

        $data['client_equipment_ids'] = [$equipmentId];
        $this->data['client_equipment_ids'] = [$equipmentId];

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<CreateOrderItemDTO>
     */
    private function buildItems(array $data): array
    {
        $serviceType = (string) $data['service_type'];
        $items = [];

        if ($serviceType === OrderServiceType::Sharpening->value) {
            foreach ($data['tools'] ?? [] as $tool) {
                $items[] = new CreateOrderItemDTO(
                    null,
                    null,
                    (string) $tool['name'],
                    (string) $tool['tool_type'],
                    (int) $tool['quantity'],
                );
            }

            return $items;
        }

        foreach ($data['client_equipment_ids'] ?? [] as $equipmentId) {
            $items[] = new CreateOrderItemDTO(
                null,
                (int) $equipmentId,
            );
        }

        return $items;
    }

    private function clearNewClientFields(Set $set): void
    {
        $set('new_client_name', null);
        $set('new_client_phone', null);
        $set('new_client_email', null);
    }

    private function clearNewEquipmentFields(Set $set): void
    {
        $set('new_equipment_title', null);
        $set('new_equipment_brand', null);
        $set('new_equipment_model_name', null);
        $set('new_equipment_notes', null);
        $set('new_equipment_parts', []);
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Создать заказ')
            ->icon(Heroicon::OutlinedCheck);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Отмена');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Заказ создан';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    public function canCreateAnother(): bool
    {
        return false;
    }
}
