<?php

namespace App\Filament\Finance\Pages;

use App\Application\Finance\Command\RegisterCashOperationCommand;
use App\Application\Finance\Command\RegisterCashOperationHandler;
use App\Application\Finance\DTO\CashDeskSummaryDTO;
use App\Application\Finance\DTO\CashOperationListItemDTO;
use App\Application\Finance\Query\GetCashDeskSummaryHandler;
use App\Application\Finance\Query\GetCashDeskSummaryQuery;
use App\Domain\Finance\VO\CashOperationType;
use App\Domain\Finance\VO\PaymentMethod;
use App\Filament\Finance\Resources\CashOperationResource;
use App\Filament\Finance\Support\PaymentPresentation;
use App\Filament\Order\Resources\OrderResource;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Carbon\Carbon;
use DateTimeImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class CashDeskDashboard extends Page
{
    use HasFiltersForm;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Финансы';

    protected static ?string $navigationLabel = 'Касса';

    protected static ?string $title = 'Касса';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'cash-desk';

    public function mount(): void
    {
        $this->filters ??= [
            'preset' => 'today',
            'payment_method' => 'all',
            'from' => null,
            'to' => null,
        ];
    }

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('preset')
                ->label('Период')
                ->options([
                    'today' => 'Сегодня',
                    'week' => 'Неделя',
                    'month' => 'Месяц',
                    'custom' => 'Свой период',
                ])
                ->default('today')
                ->required()
                ->live(),
            Select::make('payment_method')
                ->label('Способ оплаты')
                ->options(['all' => 'Все'] + PaymentMethod::options())
                ->default('all')
                ->required()
                ->live(),
            DatePicker::make('from')
                ->label('С')
                ->visible(fn (Get $get): bool => $get('preset') === 'custom')
                ->required(fn (Get $get): bool => $get('preset') === 'custom')
                ->live(),
            DatePicker::make('to')
                ->label('По')
                ->visible(fn (Get $get): bool => $get('preset') === 'custom')
                ->required(fn (Get $get): bool => $get('preset') === 'custom')
                ->live(),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('filtersForm'),
            Section::make('Итоги периода')
                ->description(fn (): string => $this->periodDescription())
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('inTotal')
                            ->label('Приход')
                            ->state(fn (): string => PaymentPresentation::formatMoney(
                                $this->summary()->inTotal,
                                $this->summary()->currency,
                            )),
                        TextEntry::make('outTotal')
                            ->label('Расход')
                            ->state(fn (): string => PaymentPresentation::formatMoney(
                                $this->summary()->outTotal,
                                $this->summary()->currency,
                            )),
                        TextEntry::make('netTotal')
                            ->label('Итого')
                            ->state(fn (): string => PaymentPresentation::formatMoney(
                                $this->summary()->netTotal,
                                $this->summary()->currency,
                            )),
                    ]),
                    Grid::make(2)->schema([
                        TextEntry::make('inCount')
                            ->label('Операций прихода')
                            ->state(fn (): int => $this->summary()->inCount),
                        TextEntry::make('outCount')
                            ->label('Операций расхода')
                            ->state(fn (): int => $this->summary()->outCount),
                    ]),
                ]),
            Section::make('Последние операции')
                ->headerActions([
                    Action::make('allOperations')
                        ->label('Все операции')
                        ->url(CashOperationResource::getUrl('index'))
                        ->link(),
                ])
                ->schema([
                    RepeatableEntry::make('recentOperations')
                        ->hiddenLabel()
                        ->state(fn (): array => array_map(
                            static fn (CashOperationListItemDTO $item): array => [
                                'id' => $item->id,
                                'type' => $item->type,
                                'payment_method' => $item->paymentMethod,
                                'order_id' => $item->orderId,
                                'order_number' => $item->orderNumber,
                                'amount' => $item->amount,
                                'currency' => $item->currency,
                                'comment' => $item->comment,
                                'registered_at' => $item->registeredAt,
                            ],
                            $this->summary()->recentOperations,
                        ))
                        ->table([
                            TableColumn::make('Тип'),
                            TableColumn::make('Заказ'),
                            TableColumn::make('Способ'),
                            TableColumn::make('Сумма'),
                            TableColumn::make('Комментарий'),
                            TableColumn::make('Когда'),
                        ])
                        ->schema([
                            TextEntry::make('type')
                                ->badge()
                                ->color(fn (?string $state): string => match (CashOperationType::tryFrom((string) $state)) {
                                    CashOperationType::In => 'success',
                                    CashOperationType::Out => 'danger',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (?string $state): string => CashOperationType::tryFrom((string) $state)?->label() ?? (string) $state),
                            TextEntry::make('order_number')
                                ->placeholder('—')
                                ->url(fn ($state, $record): ?string => is_array($record) && filled($record['order_id'] ?? null)
                                    ? OrderResource::getUrl('view', ['record' => $record['order_id']])
                                    : null)
                                ->color('primary'),
                            TextEntry::make('payment_method')
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => PaymentMethod::tryLabel($state) ?? '—'),
                            TextEntry::make('amount')
                                ->formatStateUsing(fn ($state, $record): string => PaymentPresentation::formatMoney(
                                    is_array($record) ? ($record['amount'] ?? $state) : $state,
                                    is_array($record) ? ($record['currency'] ?? 'RUB') : 'RUB',
                                )),
                            TextEntry::make('comment')->placeholder('—'),
                            TextEntry::make('registered_at')
                                ->dateTime('d.m.Y H:i'),
                        ])
                        ->placeholder('Нет операций за выбранный период'),
                ]),
        ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('registerCashOperation')
                ->label('Кассовая операция')
                ->icon(Heroicon::OutlinedPlus)
                ->form([
                    Select::make('type')
                        ->label('Тип')
                        ->options(CashOperationType::options())
                        ->required(),
                    Select::make('payment_method')
                        ->label('Способ оплаты')
                        ->options(PaymentMethod::options())
                        ->required(),
                    TextInput::make('amount')
                        ->label('Сумма')
                        ->numeric()
                        ->required(),
                    TextInput::make('comment')
                        ->label('Комментарий'),
                ])
                ->action(function (array $data): void {
                    try {
                        $id = app(SequentialEntityIdGenerator::class)->next('cash_operation')->value;
                        app(RegisterCashOperationHandler::class)->handle(new RegisterCashOperationCommand(
                            $id,
                            $data['type'],
                            (string) $data['amount'],
                            'RUB',
                            $data['comment'] ?? null,
                            paymentMethod: $data['payment_method'] ?? null,
                        ));
                        Notification::make()->title('Операция зарегистрирована')->success()->send();
                        $this->dispatch('$refresh');
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    private function summary(): CashDeskSummaryDTO
    {
        [$from, $to] = $this->resolvePeriod();

        $method = (string) ($this->filters['payment_method'] ?? 'all');

        return app(GetCashDeskSummaryHandler::class)->handle(
            new GetCashDeskSummaryQuery(
                $from,
                $to,
                paymentMethod: $method === 'all' || $method === '' ? null : $method,
            ),
        );
    }

    private function periodDescription(): string
    {
        [$from, $to] = $this->resolvePeriod();
        $method = (string) ($this->filters['payment_method'] ?? 'all');
        $methodLabel = $method === 'all' || $method === ''
            ? 'все способы'
            : (PaymentMethod::tryLabel($method) ?? $method);

        return sprintf(
            '%s — %s · %s',
            $from->format('d.m.Y H:i'),
            $to->format('d.m.Y H:i'),
            $methodLabel,
        );
    }

    /**
     * @return array{0: DateTimeImmutable, 1: DateTimeImmutable}
     */
    private function resolvePeriod(): array
    {
        $preset = (string) ($this->filters['preset'] ?? 'today');

        return match ($preset) {
            'week' => [
                DateTimeImmutable::createFromMutable(Carbon::now()->startOfWeek()->toDateTime()),
                DateTimeImmutable::createFromMutable(Carbon::now()->endOfWeek()->toDateTime()),
            ],
            'month' => [
                DateTimeImmutable::createFromMutable(Carbon::now()->startOfMonth()->toDateTime()),
                DateTimeImmutable::createFromMutable(Carbon::now()->endOfMonth()->toDateTime()),
            ],
            'custom' => $this->resolveCustomPeriod(),
            default => [
                DateTimeImmutable::createFromMutable(Carbon::now()->startOfDay()->toDateTime()),
                DateTimeImmutable::createFromMutable(Carbon::now()->endOfDay()->toDateTime()),
            ],
        };
    }

    /**
     * @return array{0: DateTimeImmutable, 1: DateTimeImmutable}
     */
    private function resolveCustomPeriod(): array
    {
        $fromRaw = $this->filters['from'] ?? null;
        $toRaw = $this->filters['to'] ?? null;

        if ($fromRaw === null || $toRaw === null || $fromRaw === '' || $toRaw === '') {
            return [
                DateTimeImmutable::createFromMutable(Carbon::now()->startOfDay()->toDateTime()),
                DateTimeImmutable::createFromMutable(Carbon::now()->endOfDay()->toDateTime()),
            ];
        }

        $from = Carbon::parse($fromRaw)->startOfDay();
        $to = Carbon::parse($toRaw)->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [
            DateTimeImmutable::createFromMutable($from->toDateTime()),
            DateTimeImmutable::createFromMutable($to->toDateTime()),
        ];
    }
}
