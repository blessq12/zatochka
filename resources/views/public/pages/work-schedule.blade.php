@extends('layouts.site')

@section('meta_description', 'График работы Заточка.ТСК.')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    <x-public.page-hero title="ГРАФИК РАБОТЫ" />

    <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-6">
            @foreach($site->scheduleDays() as $day)
                <div class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl">
                    <h2 class="absolute top-0 left-0 -translate-y-1/2 max-w-[75%] px-3 sm:px-4 bg-white dark:bg-dark-blue-500">
                        <span class="text-sm sm:text-base font-jost-bold text-[#C3006B] dark:text-white leading-tight">
                            {{ $day['name'] ?? '' }}
                        </span>
                    </h2>

                    <div class="mt-4 space-y-3">
                        @if(!empty($day['is_day_off']))
                            <p class="text-base sm:text-lg font-jost-bold text-[#C3006B] dark:text-white">
                                {{ $day['day_off_text'] ?? 'Выходной' }}
                            </p>
                        @else
                            <p class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-white">
                                {{ $day['workshop'] ?? '' }}
                            </p>
                            <p class="text-sm sm:text-base font-jost-regular text-[#C3006B] dark:text-[#C3006B] underline">
                                {{ $day['delivery'] ?? '' }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach

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
