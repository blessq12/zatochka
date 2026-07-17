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
use App\Filament\CRM\Support\RegisterClientOption;
use App\Filament\Equipment\Support\RegisterEquipmentOption;
use App\Filament\Order\Resources\OrderResource;
use App\Filament\Order\Resources\OrderResource\Support\OrderPresentation;
use App\Filament\Order\Resources\OrderResource\Support\WarrantySourceOrderSelect;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
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

                                $set('client_id', (int) $order->client_id);
                                $set('client_equipment_ids', []);
                            }),
                    ]),
            ]);
    }

    private function clientStep(): Step
    {
        return Step::make('Клиент')
            ->description('Выбор или регистрация')
            ->icon(Heroicon::OutlinedUser)
            ->schema([
                Section::make('Клиент')
                    ->schema([
                        RegisterClientOption::applyTo(OrderPresentation::clientSelect('client_id'))
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('client_equipment_ids', []))
                            ->disabled(fn (Get $get): bool => $get('billing_type') === OrderBillingType::Warranty->value
                                && filled($get('warranty_source_order_id'))),
                    ]),
            ]);
    }

    private function compositionStep(): Step
    {
        return Step::make('Состав')
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
                    ->description('Выберите оборудование клиента или зарегистрируйте новое')
                    ->icon(Heroicon::OutlinedWrenchScrewdriver)
                    ->visible(fn (Get $get): bool => $get('service_type') === OrderServiceType::Repair->value)
                    ->schema([
                        RegisterEquipmentOption::applyTo(
                            $this->clientEquipmentSelect(),
                            fn (): int => (int) ($this->data['client_id'] ?? 0),
                        )
                            ->required()
                            ->disabled(fn (Get $get): bool => blank($get('client_id'))),
                    ]),
            ]);
    }

    private function clientEquipmentSelect(): Select
    {
        return Select::make('client_equipment_ids')
            ->label('Оборудование клиента')
            ->multiple()
            ->searchable()
            ->preload()
            ->helperText('Выберите существующее или зарегистрируйте новое кнопкой справа')
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
