@extends('layouts.site')

@section('meta_description', 'График работы '.(($company['name'] ?? null) ?: 'Заточка.ТСК').'.')

@section('content')
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    <x-public.page-hero title="ГРАФИК РАБОТЫ" />

    <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-6">
            @forelse(($schedule['days'] ?? []) as $day)
                @php
                    $label = (string) ($day['label'] ?? $day['name'] ?? '');
                    $hours = (string) ($day['hours'] ?? $day['workshop'] ?? '');
                    $isDayOff = !empty($day['is_day_off'])
                        || str_contains(mb_strtolower($hours), 'выходн');
                @endphp
                <div class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl">
                    <h2 class="absolute top-0 left-0 -translate-y-1/2 max-w-[75%] px-3 sm:px-4 bg-white dark:bg-dark-blue-500">
                        <span class="text-sm sm:text-base font-jost-bold text-[#C3006B] dark:text-white leading-tight">
                            {{ $label }}
                        </span>
                    </h2>

                    <div class="mt-4 space-y-3">
                        @if($isDayOff)
                            <p class="text-base sm:text-lg font-jost-bold text-[#C3006B] dark:text-white">
                                {{ $day['day_off_text'] ?? ($hours !== '' ? $hours : 'Выходной') }}
                            </p>
                        @else
                            <p class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-white">
                                {{ $hours }}
                            </p>
                            @if(!empty($day['delivery']))
                                <p class="text-sm sm:text-base font-jost-regular text-[#C3006B] dark:text-[#C3006B] underline">
                                    {{ $day['delivery'] }}
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center font-jost-regular text-dark-gray-500 dark:text-gray-300">График скоро появится</p>
            @endforelse

            <div class="pt-8">
                @include('public.partials.order-cta', [
                    'withDeliveryHint' => true,
                    'sharpeningLabel' => 'Заточка с доставкой',
                    'repairLabel' => 'Ремонт с доставкой',
                ])
            </div>
        </div>
    </section>
</div>
@endsection
