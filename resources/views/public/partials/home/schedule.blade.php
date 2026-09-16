@php
@endphp
<section class="bg-white/80 backdrop-blur-xl text-dark-gray-500 dark:bg-dark-blue-500/90 dark:backdrop-blur-xl dark:text-gray-100">
    <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 pb-12 sm:pb-16 lg:pb-20">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 text-center mb-8 sm:mb-10">
            ГРАФИК РАБОТЫ
        </h2>

        <div class="space-y-6">
            @forelse(($schedule['days'] ?? []) as $day)
                <div class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-4 sm:px-6 pt-8 pb-4 bg-white/80 backdrop-blur-xl dark:bg-transparent">
                    <h3 class="absolute top-0 left-4 sm:left-6 -translate-y-1/2 px-3 bg-white dark:bg-dark-blue-500/90 text-lg sm:text-xl font-jost-bold uppercase tracking-wide {{ !empty($day['is_day_off']) ? 'text-[#C20A6C] dark:text-[#C20A6C]' : 'text-dark-blue-500 dark:text-dark-blue-300' }}">
                        {{ $day['name'] ?? '' }}
                    </h3>

                    <div class="space-y-2 text-sm sm:text-base">
                        @if(!empty($day['is_day_off']))
                            <p class="font-jost-bold text-[#C20A6C] dark:text-[#C20A6C]">
                                {{ $day['day_off_text'] ?? 'Выходной' }}
                            </p>
                        @else
                            <p class="font-jost-regular text-dark-gray-500 dark:text-gray-200">
                                {{ $day['workshop'] ?? '' }}
                            </p>
                            <p class="font-jost-regular text-[#C20A6C] dark:text-[#C20A6C]">
                                {{ $day['delivery'] ?? '' }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center font-jost-regular text-dark-gray-500 dark:text-gray-300">График скоро появится</p>
            @endforelse
        </div>

        <div class="mt-10">
            @include('public.partials.order-cta', [
                'withDeliveryHint' => true,
                'sharpeningLabel' => 'Заточка с доставкой',
                'repairLabel' => 'Ремонт с доставкой',
            ])
        </div>
    </div>
</section>
