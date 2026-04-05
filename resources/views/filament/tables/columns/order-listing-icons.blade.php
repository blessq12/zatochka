@php
    use App\Models\Order;

    /** @var \Illuminate\Database\Eloquent\Model|null $record */
    $record = isset($record) ? $record : $getRecord();
    $serviceType = $record->service_type;
    $paymentType = $record->order_payment_type;
    $urgency = $record->urgency;

    $serviceIcon = match ($serviceType) {
        Order::TYPE_SHARPENING => 'heroicon-o-scissors',
        Order::TYPE_REPAIR => 'heroicon-o-wrench-screwdriver',
        Order::TYPE_DIAGNOSTIC => 'heroicon-o-magnifying-glass',
        Order::TYPE_CONSULTATION => 'heroicon-o-chat-bubble-left-right',
        Order::TYPE_MAINTENANCE => 'heroicon-o-cog-6-tooth',
        Order::TYPE_WARRANTY => 'heroicon-o-shield-check',
        Order::TYPE_REPLACEMENT => 'heroicon-o-arrow-path',
        default => 'heroicon-o-question-mark-circle',
    };
    $serviceColor = match ($serviceType) {
        Order::TYPE_REPAIR => 'primary',
        Order::TYPE_SHARPENING => 'success',
        Order::TYPE_DIAGNOSTIC => 'warning',
        Order::TYPE_CONSULTATION => 'info',
        Order::TYPE_MAINTENANCE => 'gray',
        Order::TYPE_WARRANTY => 'danger',
        Order::TYPE_REPLACEMENT => 'primary',
        default => 'gray',
    };

    $paymentIcon = match ($paymentType) {
        Order::PAYMENT_TYPE_PAID => 'heroicon-o-banknotes',
        Order::PAYMENT_TYPE_WARRANTY => 'heroicon-o-shield-check',
        default => 'heroicon-o-question-mark-circle',
    };
    $paymentColor = match ($paymentType) {
        Order::PAYMENT_TYPE_PAID => 'success',
        Order::PAYMENT_TYPE_WARRANTY => 'warning',
        default => 'gray',
    };

    $urgencyIcon = match ($urgency) {
        Order::URGENCY_URGENT => 'heroicon-o-exclamation-triangle',
        Order::URGENCY_NORMAL => 'heroicon-o-clock',
        default => 'heroicon-o-question-mark-circle',
    };
    $urgencyColor = match ($urgency) {
        Order::URGENCY_URGENT => 'danger',
        Order::URGENCY_NORMAL => 'primary',
        default => 'gray',
    };

    $serviceTip = Order::getAvailableTypes()[$serviceType] ?? $serviceType;
    $paymentTip = match ($paymentType) {
        Order::PAYMENT_TYPE_PAID => 'Платный',
        Order::PAYMENT_TYPE_WARRANTY => 'Гарантийный',
        default => (string) $paymentType,
    };
    $urgencyTip = Order::getAvailableUrgencies()[$urgency] ?? $urgency;
@endphp

<div class="fi-ta-icon flex flex-nowrap items-center gap-1">
    <span title="{{ $serviceTip }}">
        <x-filament::icon
            :icon="$serviceIcon"
            @class([
                'fi-ta-icon-item h-5 w-5',
                match ($serviceColor) {
                    'gray' => 'text-gray-400 dark:text-gray-500',
                    default => 'fi-color-custom text-custom-500 dark:text-custom-400',
                },
                is_string($serviceColor) ? 'fi-color-' . $serviceColor : null,
            ])
            @style([
                \Filament\Support\get_color_css_variables(
                    $serviceColor,
                    shades: [400, 500],
                    alias: 'tables::columns.icon-column.item',
                ) => $serviceColor !== 'gray',
            ])
        />
    </span>
    <span title="{{ $paymentTip }}">
        <x-filament::icon
            :icon="$paymentIcon"
            @class([
                'fi-ta-icon-item h-5 w-5',
                match ($paymentColor) {
                    'gray' => 'text-gray-400 dark:text-gray-500',
                    default => 'fi-color-custom text-custom-500 dark:text-custom-400',
                },
                is_string($paymentColor) ? 'fi-color-' . $paymentColor : null,
            ])
            @style([
                \Filament\Support\get_color_css_variables(
                    $paymentColor,
                    shades: [400, 500],
                    alias: 'tables::columns.icon-column.item',
                ) => $paymentColor !== 'gray',
            ])
        />
    </span>
    <span title="{{ $urgencyTip }}">
        <x-filament::icon
            :icon="$urgencyIcon"
            @class([
                'fi-ta-icon-item h-5 w-5',
                match ($urgencyColor) {
                    'gray' => 'text-gray-400 dark:text-gray-500',
                    default => 'fi-color-custom text-custom-500 dark:text-custom-400',
                },
                is_string($urgencyColor) ? 'fi-color-' . $urgencyColor : null,
            ])
            @style([
                \Filament\Support\get_color_css_variables(
                    $urgencyColor,
                    shades: [400, 500],
                    alias: 'tables::columns.icon-column.item',
                ) => $urgencyColor !== 'gray',
            ])
        />
    </span>
</div>
