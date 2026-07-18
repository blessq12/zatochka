<?php

namespace App\Filament\Finance\Resources;

use App\Domain\Finance\VO\CashOperationType;
use App\Domain\Finance\VO\PaymentMethod;
use App\Filament\Finance\Resources\CashOperationResource\Pages\ListCashOperations;
use App\Filament\Finance\Resources\CashOperationResource\Pages\ViewCashOperation;
use App\Filament\Finance\Support\PaymentPresentation;
use App\Filament\Order\Resources\OrderResource;
use App\Filament\Support\DomainResource;
use App\Infrastructure\Finance\Model\CashOperationModel;
use App\Infrastructure\Finance\Model\PaymentModel;
use App\Infrastructure\Finance\Model\RefundModel;
use App\Infrastructure\Order\Model\OrderModel;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CashOperationResource extends DomainResource
{
    protected static ?string $model = CashOperationModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Финансы';

    protected static ?string $navigationLabel = 'Кассовые операции';

    protected static ?string $modelLabel = 'Кассовая операция';

    protected static ?string $pluralModelLabel = 'Кассовые операции';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?int $navigationSort = 21;

    protected static bool $shouldRegisterNavigation = false;

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id')->label('ID'),
            TextEntry::make('type')
                ->label('Тип')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => CashOperationType::tryFrom((string) $state)?->label() ?? (string) $state),
            TextEntry::make('payment_method')
                ->label('Способ оплаты')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => PaymentMethod::tryLabel($state) ?? '—'),
            TextEntry::make('order_number')
                ->label('Заказ')
                ->state(fn (CashOperationModel $record): string => self::orderNumber($record) ?? '—')
                ->url(fn (CashOperationModel $record): ?string => ($orderId = self::orderId($record)) !== null
                    ? OrderResource::getUrl('view', ['record' => $orderId])
                    : null)
                ->color('primary'),
            TextEntry::make('amount')
                ->label('Сумма')
                ->formatStateUsing(fn ($state, CashOperationModel $record): string => PaymentPresentation::formatMoney(
                    (string) $state,
                    (string) $record->currency,
                )),
            TextEntry::make('currency')->label('Валюта'),
            TextEntry::make('comment')->label('Комментарий')->placeholder('—'),
            TextEntry::make('payment_id')->label('Платёж')->placeholder('—'),
            TextEntry::make('refund_id')->label('Возврат')->placeholder('—'),
            TextEntry::make('registered_at')->label('Зарегистрировано')->dateTime(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with([
                    'payment.order:id,number',
                    'refund.payment.order:id,number',
                ]))
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (?string $state): string => match (CashOperationType::tryFrom((string) $state)) {
                        CashOperationType::In => 'success',
                        CashOperationType::Out => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => CashOperationType::tryFrom((string) $state)?->label() ?? (string) $state)
                    ->searchable(),
                TextColumn::make('order_number')
                    ->label('Заказ')
                    ->state(fn (CashOperationModel $record): string => self::orderNumber($record) ?? '—')
                    ->url(fn (CashOperationModel $record): ?string => ($orderId = self::orderId($record)) !== null
                        ? OrderResource::getUrl('view', ['record' => $orderId])
                        : null)
                    ->color('primary')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $inner) use ($search): void {
                            $inner->whereHas('payment.order', fn (Builder $orders): Builder => $orders->where('number', 'like', "%{$search}%"))
                                ->orWhereHas('refund.payment.order', fn (Builder $orders): Builder => $orders->where('number', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('payment_method')
                    ->label('Способ')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => PaymentMethod::tryLabel($state) ?? '—'),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->formatStateUsing(fn ($state, CashOperationModel $record): string => PaymentPresentation::formatMoney(
                        (string) $state,
                        (string) $record->currency,
                    )),
                TextColumn::make('comment')->label('Комментарий')->limit(40)->placeholder('—'),
                TextColumn::make('registered_at')->label('Когда')->dateTime()->sortable(),
            ])
            ->defaultSort('registered_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(CashOperationType::options()),
                SelectFilter::make('payment_method')
                    ->label('Способ оплаты')
                    ->options(PaymentMethod::options()),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashOperations::route('/'),
            'view' => ViewCashOperation::route('/{record}'),
        ];
    }

    private static function orderNumber(CashOperationModel $record): ?string
    {
        $order = self::order($record);

        return $order !== null ? (string) $order->number : null;
    }

    private static function orderId(CashOperationModel $record): ?string
    {
        $order = self::order($record);

        return $order !== null ? (string) $order->id : null;
    }

    private static function order(CashOperationModel $record): ?OrderModel
    {
        if ($record->payment_id !== null) {
            /** @var PaymentModel|null $payment */
            $payment = $record->relationLoaded('payment')
                ? $record->getRelation('payment')
                : PaymentModel::query()->with('order:id,number')->find($record->payment_id);

            return $payment?->order;
        }

        if ($record->refund_id !== null) {
            /** @var RefundModel|null $refund */
            $refund = $record->relationLoaded('refund')
                ? $record->getRelation('refund')
                : RefundModel::query()->with('payment.order:id,number')->find($record->refund_id);

            return $refund?->payment?->order;
        }

        return null;
    }
}
