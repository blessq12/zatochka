@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
    /** @var list<array<string, mixed>> $prices */
@endphp
<div class="space-y-4">
    @forelse($prices as $item)
        <div class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-4 sm:px-6 py-4 bg-white/80 dark:bg-transparent">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                <div>
                    <h3 class="text-base sm:text-lg font-jost-bold text-dark-blue-500 dark:text-dark-blue-300">
                        {{ $item['name'] ?? '' }}
                    </h3>
                    @if(!empty($item['description']))
                        <p class="mt-1 text-sm font-jost-regular text-dark-gray-500 dark:text-gray-300">
                            {{ $item['description'] }}
                        </p>
                    @endif
                </div>
                <p class="text-base sm:text-lg font-jost-bold text-[#C3006B] whitespace-nowrap">
                    {{ $site->formatPrice($item) }}
                </p>
            </div>
        </div>
    @empty
        <p class="text-center text-dark-gray-500 dark:text-gray-300 font-jost-regular">
            Прайс временно недоступен.
        </p>
    @endforelse
</div>
