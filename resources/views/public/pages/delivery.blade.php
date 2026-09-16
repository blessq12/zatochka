@extends('layouts.site')

@section('meta_description', 'Доставка инструментов: заберём и вернём после заточки или ремонта. Заточка.ТСК, Томск.')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
    $delivery = $site->deliveryInfo();
    $conditions = array_values((array) ($delivery['free_conditions'] ?? []));
    $advantages = array_values((array) ($delivery['advantages'] ?? []));
@endphp
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    @include('public.partials.page-hero', ['title' => 'ДОСТАВКА'])

    <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-10">
            <div class="text-center space-y-3">
                <p class="text-base sm:text-lg font-jost-regular text-dark-gray-500 dark:text-gray-200">
                    Курьер <span class="font-jost-bold">забирает</span> инструменты по вашему адресу и
                    <span class="font-jost-bold">привозит обратно</span> после работы.
                </p>
                <p class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300">
                    Заявка — на заточку или ремонт; доставка отмечается в форме.
                </p>
            </div>

            <div class="pt-2" id="high">
                @include('public.partials.order-cta', [
                    'layout' => 'stack',
                    'sharpeningLabel' => 'Заточка с доставкой',
                    'repairLabel' => 'Ремонт с доставкой',
                ])
            </div>

            <div class="px-6 pt-6 pb-6 sm:px-10 sm:pt-8 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl space-y-6">
                <h3 class="text-lg sm:text-xl font-jost-bold text-[#C3006B]">УСЛОВИЯ БЕСПЛАТНОЙ ДОСТАВКИ</h3>
                <div class="space-y-3">
                    @forelse($conditions as $condition)
                        <p class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200 text-start">
                            {{ is_array($condition) ? ($condition['text'] ?? '') : $condition }}
                        </p>
                    @empty
                        <p class="text-sm font-jost-regular text-dark-gray-500 dark:text-gray-300">Условия уточняйте у менеджера.</p>
                    @endforelse
                </div>
            </div>

            <div class="pt-2" id="low">
                @include('public.partials.order-cta', [
                    'layout' => 'row',
                    'sharpeningLabel' => 'Оставить заявку на заточку',
                    'repairLabel' => 'Оставить заявку на ремонт',
                ])
            </div>

            <div class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl mt-24">
                <h2 class="absolute top-0 left-0 -translate-y-1/2 max-w-[75%] px-3 sm:px-4 bg-white dark:bg-dark-blue-500">
                    <span class="text-sm sm:text-base font-jost-bold text-[#C3006B] leading-tight">ПРЕИМУЩЕСТВА НАШЕЙ ДОСТАВКИ</span>
                </h2>
                <div class="space-y-6 mt-4">
                    @foreach($advantages as $advantage)
                        <div class="flex items-start gap-3">
                            <div>
                                <p class="text-sm sm:text-base font-jost-bold text-dark-gray-500 dark:text-gray-200">
                                    {{ is_array($advantage) ? ($advantage['title'] ?? '') : '' }}
                                </p>
                                <p class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300 mt-1">
                                    {{ is_array($advantage) ? ($advantage['description'] ?? '') : $advantage }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
