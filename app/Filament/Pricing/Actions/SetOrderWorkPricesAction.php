<?php

namespace App\Filament\Pricing\Actions;

use App\Application\Pricing\Command\SetOrderWorkPricesCommand;
use App\Application\Pricing\Command\SetOrderWorkPricesHandler;
use App\Application\Pricing\ServiceType\WorkPricingPolicyResolver;
use App\Domain\Order\VO\OrderStatus;
use App\Filament\Order\Resources\OrderResource\Support\OrderWorkPricing;
use App\Infrastructure\Order\Model\OrderModel;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

final class SetOrderWorkPricesAction
{
    public static function make(): Action
    {
        return Action::make('setOrderPrices')
            ->label('Назначить цены')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('warning')
            ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::WorksCompleted->value)
            ->modalHeading('Стоимость выполненных работ')
            ->modalDescription(fn (OrderModel $record): string => app(WorkPricingPolicyResolver::class)
                ->forValue((string) $record->service_type)
                ->modalDescription())
            ->fillForm(fn (OrderModel $record): array => [
                'work_prices' => OrderWorkPricing::buildWorkPricesFormDefaults($record),
            ])
            ->form(fn (OrderModel $record): array => [
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

                                return OrderWorkPricing::formatMoney((string) round($unitAmount * $quantity, 2));
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
                    if (OrderWorkPricing::buildWorkPricesFormDefaults($record) === []) {
                        Notification::make()
                            ->title('Нет выполненных работ для оценки')
                            ->danger()
                            ->send();

                        return;
                    }

                    app(SetOrderWorkPricesHandler::class)->handle(new SetOrderWorkPricesCommand(
                        (string) $record->id,
                        array_map(
                            static fn (array $row): array => [
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
            });
    }
}
