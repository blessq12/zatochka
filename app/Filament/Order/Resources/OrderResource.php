<?php

namespace App\Filament\Order\Resources;

use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
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
                    ->label('Номер')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client_id')
                    ->label('Клиент')
                    ->formatStateUsing(function (?int $state, OrderModel $record): string {
                        $client = $record->client;

                        if ($client === null) {
                            return $state !== null ? '#'.$state : '—';
                        }

                        return trim(($client->name ?: 'Без имени').' · '.$client->phone);
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('client', function (Builder $client) use ($search): void {
                            $client->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                TextColumn::make('service_type')
                    ->label('Тип')
                    ->formatStateUsing(fn (?string $state): string => OrderServiceType::tryLabel($state) ?? ($state ?? '—'))
                    ->sortable(),
                TextColumn::make('billing_type')
                    ->label('Вид')
                    ->formatStateUsing(fn (?string $state): string => OrderBillingType::tryLabel($state) ?? ($state ?? '—'))
                    ->toggleable(),
                TextColumn::make('urgency')
                    ->label('Срочность')
                    ->formatStateUsing(fn (?string $state): string => OrderUrgency::tryLabel($state) ?? ($state ?? '—'))
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => OrderStatus::tryLabel($state) ?? ($state ?? '—'))
                    ->color(fn (?string $state): string => OrderStatus::tryColor($state))
                    ->sortable(),
                TextColumn::make('estimated_amount')
                    ->label('Сумма')
                    ->formatStateUsing(fn (?string $state): string => $state !== null ? $state.' ₽' : '—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()->label('Просмотр'),
                ...static::orderMutationActions(),
            ]);
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
