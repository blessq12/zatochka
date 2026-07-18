<?php

namespace App\Filament\Order\Resources\OrderResource\Support;

use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderServiceType;
use App\Infrastructure\Order\Model\OrderModel;
use Filament\Forms\Components\Select;

/**
 * Селект исходного заказа для оформления гарантии.
 */
final class WarrantySourceOrderSelect
{
    public static function make(string $name = 'warranty_source_order_id'): Select
    {
        return Select::make($name)
            ->label('Заказ по гарантии')
            ->placeholder('Выберите заказ')
            ->searchable()
            ->preload()
            ->options(fn (): array => self::options())
            ->getSearchResultsUsing(fn (string $search): array => self::options($search))
            ->getOptionLabelUsing(fn ($value): ?string => self::label($value));
    }

    /** @return array<int, string> */
    public static function options(?string $search = null): array
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
            ->mapWithKeys(static fn (OrderModel $order): array => [
                (string) $order->id => self::format($order),
            ])
            ->all();
    }

    public static function label(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $order = OrderModel::query()->with('client')->find((string) $value);

        return $order === null ? null : self::format($order);
    }

    public static function format(OrderModel $order): string
    {
        $client = $order->client;
        $clientLabel = $client === null
            ? 'Клиент #'.$order->client_id
            : trim(($client->name ?: 'Без имени').' · '.$client->phone);

        $type = OrderServiceType::tryLabel($order->service_type) ?? $order->service_type;

        return (string) OrderPresentation::orderNumber($order).' · '.$type.' · '.$clientLabel;
    }
}
