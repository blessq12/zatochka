@php
/** @var list<array<string, mixed>> $prices */
@endphp
<div
    class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 py-6 sm:px-10 sm:py-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
>
    <div class="space-y-4">
        @forelse($prices as $item)
            <div class="flex justify-between items-center gap-2 sm:gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-base sm:text-lg font-jost-regular text-dark-gray-500 dark:text-white">
                        {{ $item['name'] ?? '' }}
                    </p>
                    @if(!empty($item['description']))
                        <p class="text-xs sm:text-sm font-jost-regular text-dark-gray-400 dark:text-gray-300 mt-1">
                            {{ $item['description'] }}
                        </p>
                    @endif
                </div>
                @if(!empty($item['price']))
                    <p class="text-lg sm:text-xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C] flex-shrink-0">
                        {{ ($item['display_price'] ?? '') }}
                    </p>
                @endif
            </div>
        @empty
            <p class="text-center font-jost-regular text-dark-gray-500 dark:text-gray-300">Прайс скоро появится</p>
        @endforelse
    </div>
</div>
