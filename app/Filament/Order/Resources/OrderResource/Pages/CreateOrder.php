<?php

namespace App\Filament\Order\Resources\OrderResource\Pages;

use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Filament\Order\Resources\OrderResource;
use App\Filament\Order\Resources\OrderResource\Support\OrderPresentation;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
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
            Step::make('Параметры')
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
                                    } else {
                                        $set('client_mode', 'existing');
                                        $set('new_client_name', null);
                                        $set('new_client_phone', null);
                                        $set('new_client_email', null);
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
                            Select::make('warranty_source_order_id')
                                ->label('Заказ по гарантии')
                                ->placeholder('Выберите заказ')
                                ->searchable()
                                ->preload()
                                ->options(fn (): array => $this->warrantySourceOrderOptions())
                                ->getSearchResultsUsing(fn (string $search): array => $this->warrantySourceOrderOptions($search))
                                ->getOptionLabelUsing(fn ($value): ?string => $this->warrantySourceOrderLabel($value))
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
                                    $set('client_equipment_ids', []);
                                    $set('equipment_mode', 'existing');
                                }),
                        ]),
                ]),
            Step::make('Клиент')
                ->description('Существующий или новый')
                ->icon(Heroicon::OutlinedUser)
                ->schema([
                    Section::make('Клиент')
                        ->schema([
                            Radio::make('client_mode')
                                ->label('Как указать клиента')
                                ->options([
                                    'existing' => 'Существующий клиент',
                                    'new' => 'Новый клиент',
                                ])
                                ->descriptions([
                                    'existing' => 'Выбор из базы клиентов',
                                    'new' => 'Регистрация при создании заказа',
                                ])
                                ->default('existing')
                                ->required()
                                ->live()
                                ->disabled(fn (Get $get): bool => $get('billing_type') === OrderBillingType::Warranty->value)
                                ->afterStateUpdated(function (mixed $state, Set $set): void {
                                    if ($state === 'new') {
                                        $set('client_id', null);
                                        $set('equipment_mode', 'new');
                                        $set('client_equipment_ids', []);
                                    }
                                }),
                            OrderPresentation::clientSelect('client_id')
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('client_equipment_ids', []))
                                ->visible(fn (Get $get): bool => $get('client_mode') === 'existing')
                                ->required(fn (Get $get): bool => $get('client_mode') === 'existing')
                                ->disabled(fn (Get $get): bool => $get('billing_type') === OrderBillingType::Warranty->value
                                    && filled($get('warranty_source_order_id'))),
                            Grid::make(2)
                                ->visible(fn (Get $get): bool => $get('client_mode') === 'new'
                                    && $get('billing_type') !== OrderBillingType::Warranty->value)
                                ->schema([
                                    TextInput::make('new_client_name')
                                        ->label('ФИО')
                                        ->maxLength(255)
                                        ->required(fn (Get $get): bool => $get('client_mode') === 'new'),
                                    TextInput::make('new_client_phone')
                                        ->label('Телефон')
                                        ->tel()
                                        ->telRegex('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/')
                                        ->mask('+7 (999) 999-99-99')
                                        ->placeholder('+7 (___) ___-__-__')
                                        ->required(fn (Get $get): bool => $get('client_mode') === 'new'),
                                    TextInput::make('new_client_email')
                                        ->label('Эл. почта')
                                        ->email()
                                        ->maxLength(255)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ]),
            Step::make('Состав')
                ->description('Инструменты или оборудование')
                ->icon(Heroicon::OutlinedCube)
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
                        ->description('Выберите существующее или зарегистрируйте новое')
                        ->icon(Heroicon::OutlinedWrenchScrewdriver)
                        ->visible(fn (Get $get): bool => $get('service_type') === OrderServiceType::Repair->value)
                        ->schema([
                            ToggleButtons::make('equipment_mode')
                                ->label('Источник оборудования')
                                ->options([
                                    'existing' => 'У клиента',
                                    'new' => 'Новое',
                                ])
                                ->icons([
                                    'existing' => Heroicon::OutlinedArchiveBox,
                                    'new' => Heroicon::OutlinedPlusCircle,
                                ])
                                ->grouped()
                                ->default('existing')
                                ->live()
                                ->required(),
                            Select::make('client_equipment_ids')
                                ->label('Оборудование клиента')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(function (Get $get): array {
                                    if ($get('client_mode') !== 'existing' || blank($get('client_id'))) {
                                        return [];
                                    }

                                    return ClientEquipmentModel::query()
                                        ->where('client_id', (int) $get('client_id'))
                                        ->orderBy('title')
                                        ->get()
                                        ->mapWithKeys(static function (ClientEquipmentModel $equipment): array {
                                            $label = trim($equipment->title.' · '.$equipment->brand.' '.$equipment->model_name);

                                            return [(int) $equipment->id => $label];
                                        })
                                        ->all();
                                })
                                ->visible(fn (Get $get): bool => $get('equipment_mode') === 'existing')
                                ->required(fn (Get $get): bool => $get('equipment_mode') === 'existing')
                                ->disabled(fn (Get $get): bool => $get('client_mode') !== 'existing' || blank($get('client_id'))),
                            Repeater::make('new_equipment')
                                ->label('Новое оборудование')
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Название')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('brand')
                                        ->label('Бренд')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('model_name')
                                        ->label('Модель')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('notes')
                                        ->label('Заметки')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                    Repeater::make('parts')
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
                                        ->collapsible(),
                                ])
                                ->columns(3)
                                ->defaultItems(1)
                                ->minItems(1)
                                ->addActionLabel('Добавить оборудование')
                                ->cloneable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => filled($state['title'] ?? null)
                                    ? (string) $state['title']
                                    : 'Оборудование')
                                ->visible(fn (Get $get): bool => $get('equipment_mode') === 'new')
                                ->required(fn (Get $get): bool => $get('equipment_mode') === 'new'),
                        ]),
                ]),
            Step::make('Приёмка')
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
                ]),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $orderId = OrderId::generate()->value;
            $items = $this->buildItems($data);

            $isWarranty = ($data['billing_type'] ?? null) === OrderBillingType::Warranty->value;
            $isNewClient = ! $isWarranty && ($data['client_mode'] ?? null) === 'new';

            app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
                $orderId,
                $isNewClient ? 0 : (int) ($data['client_id'] ?? 0),
                (string) $data['estimated_amount'],
                $items,
                (string) $data['service_type'],
                (string) $data['billing_type'],
                (string) $data['urgency'],
                (bool) ($data['delivery_required'] ?? false),
                $data['defects'] ?? null,
                $data['internal_notes'] ?? null,
                'RUB',
                $isNewClient ? ($data['new_client_name'] ?? null) : null,
                $isNewClient ? ($data['new_client_phone'] ?? null) : null,
                $isNewClient ? ($data['new_client_email'] ?? null) : null,
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

        if (($data['equipment_mode'] ?? null) === 'existing') {
            foreach ($data['client_equipment_ids'] ?? [] as $equipmentId) {
                $items[] = new CreateOrderItemDTO(
                    null,
                    (int) $equipmentId,
                );
            }

            return $items;
        }

        foreach ($data['new_equipment'] ?? [] as $equipment) {
            $parts = [];

            foreach ($equipment['parts'] ?? [] as $part) {
                if (! filled($part['name'] ?? null)) {
                    continue;
                }

                $parts[] = [
                    'name' => (string) $part['name'],
                    'serialNumber' => filled($part['serialNumber'] ?? null)
                        ? (string) $part['serialNumber']
                        : null,
                ];
            }

            $items[] = new CreateOrderItemDTO(
                null,
                null,
                null,
                null,
                null,
                (string) $equipment['title'],
                (string) $equipment['brand'],
                (string) $equipment['model_name'],
                filled($equipment['notes'] ?? null) ? (string) $equipment['notes'] : null,
                $parts,
            );
        }

        return $items;
    }

    /** @return array<int, string> */
    private function warrantySourceOrderOptions(?string $search = null): array
    {
        $query = OrderModel::query()
            ->with('client')
            ->where('billing_type', '!=', OrderBillingType::Warranty->value)
            ->orderByDesc('created_at')
            ->limit(50);

        if (filled($search)) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('id', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($client) use ($search): void {
                        $client->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        return $query->get()
            ->mapWithKeys(fn (OrderModel $order): array => [
                (string) $order->id => $this->formatWarrantySourceOrder($order),
            ])
            ->all();
    }

    private function warrantySourceOrderLabel(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $order = OrderModel::query()->with('client')->find((string) $value);

        return $order === null ? null : $this->formatWarrantySourceOrder($order);
    }

    private function formatWarrantySourceOrder(OrderModel $order): string
    {
        $client = $order->client;
        $clientLabel = $client === null
            ? 'Клиент #'.$order->client_id
            : trim(($client->name ?: 'Без имени').' · '.$client->phone);

        $type = OrderPresentation::serviceTypeOptions()[$order->service_type] ?? $order->service_type;

        return (string) OrderPresentation::orderNumber($order).' · '.$type.' · '.$clientLabel;
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
