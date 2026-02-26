<x-filament-widgets::widget>
    @php($metrics = $this->getMetrics())

    <x-filament::section>
        <x-slot name="heading">
            План выручки за месяц
        </x-slot>

        <div class="space-y-3">
            <div class="flex items-baseline justify-between">
                <div class="text-sm text-gray-500">
                    Период: {{ $metrics['monthLabel'] }}
                </div>

                <div class="text-sm font-medium text-gray-900">
                    {{ $metrics['revenueFormatted'] }}
                    @if ($metrics['hasPlan'])
                        <span class="text-gray-500"> / {{ $metrics['planFormatted'] }}</span>
                    @endif
                </div>
            </div>

            @if ($metrics['hasPlan'])
                <div class="w-full h-3 rounded-full bg-gray-200 overflow-hidden">
                    <div
                        class="h-3 rounded-full bg-primary-500 transition-all"
                        style="width: {{ $metrics['progress'] }}%;"
                    ></div>
                </div>

                <div class="flex justify-between text-xs text-gray-500">
                    <span>0%</span>
                    <span>{{ $metrics['progress'] }}%</span>
                    <span>100%</span>
                </div>
            @else
                <p class="text-xs text-warning-600">
                    План выручки на этот месяц не задан. Создайте его в разделе «Планы выручки».
                </p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

