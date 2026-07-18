<?php

namespace App\Filament\Order\Resources;

use App\Domain\Order\VO\OrderSource;
use App\Domain\Order\VO\OrderStatus;
use App\Filament\Order\Resources\OrderResource\Actions\OrderMutationActions;
use App\Filament\Order\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Order\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Order\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Order\Resources\OrderResource\Support\OrderInfolist;
use App\Filament\Order\Resources\OrderResource\Support\OrderPresentation;
use App\Filament\Support\DomainResource;
use App\Infrastructure\Order\Model\OrderModel;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class OrderResource extends DomainResource
{
    protected static ?string $model = OrderModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Заказы';

    protected static ?string $navigationLabel = 'Заказы';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    protected static ?int $navigationSort = 10;

    public static function hasRecordTitle(): bool
    {
        return true;
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        if (! $record instanceof OrderModel) {
            return static::getModelLabel();
        }

        return (string) OrderPresentation::orderNumber($record);
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canView(Model $record): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['client', 'items.equipment.components', 'warrantySourceOrder.client']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(OrderInfolist::components());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Номер заказа')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->state(fn (OrderModel $record): array => [
                        (string) $record->status,
                        (string) ($record->source ?? OrderSource::Admin->value),
                    ])
                    ->badge()
                    ->listWithLineBreaks()
                    ->formatStateUsing(fn (string $state): string => OrderStatus::tryLabel($state)
                        ?? OrderSource::tryLabel($state)
                        ?? $state)
                    ->color(fn (string $state): string => OrderStatus::tryFrom($state) !== null
                        ? OrderStatus::tryColor($state)
                        : OrderSource::tryColor($state))
                    ->sortable(),
                TextColumn::make('client_id')
                    ->label('Клиент')
                    ->formatStateUsing(fn (?int $state, OrderModel $record): string => OrderPresentation::clientListingName($record))
                    ->description(fn (OrderModel $record): string => OrderPresentation::clientListingPhone($record))
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('client', function (Builder $client) use ($search): void {
                            $client->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                TextColumn::make('type_flags')
                    ->label('Тип / Вид / Срочность')
                    ->state(fn (OrderModel $record): Htmlable => OrderPresentation::typeFlagsHtml($record))
                    ->html()
                    ->alignCenter(),
                TextColumn::make('estimated_amount')
                    ->label('Стоимость')
                    ->formatStateUsing(fn (?string $state): string => $state !== null ? $state.' ₽' : '—')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions(static::tableRecordActions(), RecordActionsPosition::BeforeColumns)
            ->recordActionsColumnLabel('');
    }

    /**
     * @return list<Action>
     */
    private static function tableRecordActions(): array
    {
        return [
            ViewAction::make()
                ->label('Просмотр')
                ->icon(Heroicon::OutlinedEye)
                ->iconButton()
                ->tooltip('Просмотр'),
        ];
    }

    /** @return list<Action> */
    public static function orderMutationActions(): array
    {
        return OrderMutationActions::all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
