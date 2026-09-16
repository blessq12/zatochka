@extends('layouts.site')

@section('meta_description', 'Профессиональная заточка маникюрных, парикмахерских и грумерских инструментов и ремонт оборудования в Томске. Более 5 лет опыта.')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<div class="bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500">
    <section class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-12 sm:py-16 lg:py-20">
        <div class="flex flex-col items-center text-center space-y-4 mb-10">
            <p class="text-2xl sm:text-3xl font-jost-bold text-[#C20A6C] tracking-wide">ЗАТОЧКА.ТСК</p>
            <p class="text-xs sm:text-sm font-jost-regular text-dark-gray-500 dark:text-gray-300 tracking-wide">
                ПОРА ЗАТОЧИТЬ ИНСТРУМЕНТЫ
            </p>
        </div>

        <div class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 mb-10 bg-white/80 dark:bg-dark-blue-500/90 mt-16">
            <h1 class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[90%] px-4 sm:px-6 bg-white dark:bg-dark-blue-500/90 font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 text-center text-xs md:text-sm">
                ПРОФЕССИОНАЛЬНАЯ ЗАТОЧКА ИНСТРУМЕНТОВ
            </h1>
            <p class="text-sm sm:text-base lg:text-lg font-jost-regular text-dark-gray-500 dark:text-gray-200 text-center">
                Заточка маникюрных, парикмахерских, грумерских инструментов и ремонт оборудования.
                Более 5 лет опыта и более 40 000 восстановленных инструментов.
            </p>
        </div>

        <div class="flex flex-col space-y-4 max-w-md mx-auto">
            <a href="{{ url('/sharpening') }}" class="w-full bg-dark-blue-500 hover:bg-dark-blue-600 text-white text-center px-8 py-4 font-jost-bold text-lg">ЗАКАЗАТЬ ЗАТОЧКУ</a>
            <a href="{{ url('/repair') }}" class="w-full bg-dark-blue-500 hover:bg-dark-blue-600 text-white text-center px-8 py-4 font-jost-bold text-lg">ЗАКАЗАТЬ РЕМОНТ</a>
            <a href="{{ url('/delivery') }}" class="w-full bg-pink-500 hover:bg-pink-600 text-white text-center px-8 py-4 font-jost-bold text-lg">ДОСТАВКА: ЗАБЕРЁМ И ВЕРНЁМ</a>
            <a href="{{ url('/contacts') }}" class="w-full bg-dark-blue-500 hover:bg-dark-blue-600 text-white text-center px-8 py-4 font-jost-bold text-lg">СВЯЗАТЬСЯ С НАМИ</a>
        </div>
    </section>

    <section class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 pb-12 sm:pb-16">
        <div class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 dark:bg-dark-blue-500">
            <h2 class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 px-4 sm:px-6 bg-white dark:bg-dark-blue-500 text-lg sm:text-xl font-jost-bold text-[#C20A6C] text-center">
                ПОЧЕМУ ВЫБИРАЮТ НАС
            </h2>
            <ul class="space-y-4 text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200">
                <li>Опыт более 5 лет и десятки тысяч заточенных инструментов.</li>
                <li>Работаем с профессиональным beauty-инструментом и оборудованием.</li>
                <li>Прозрачные сроки, прайс и статус заказа в личном кабинете.</li>
                <li>Доставка: заберём и вернём по Томску.</li>
            </ul>
        </div>
    </section>

    @if($site->reviews !== null && count($site->reviews['items'] ?? []) > 0)
        <section class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 pb-12 sm:pb-16">
            <h2 class="text-2xl sm:text-3xl font-jost-bold text-[#C20A6C] text-center mb-8">ОТЗЫВЫ КЛИЕНТОВ</h2>
            @if(!empty($site->reviews['average_rating']))
                <p class="text-center text-dark-gray-500 dark:text-gray-300 mb-6 font-jost-regular">
                    Средняя оценка: {{ $site->reviews['average_rating'] }}
                </p>
            @endif
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($site->reviews['items'] as $review)
                    <article class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 p-4 sm:p-6 bg-white/80 dark:bg-transparent">
                        <p class="font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-2">
                            {{ $review['client_name'] ?? 'Клиент' }}
                            @if(!empty($review['rating']))
                                <span class="text-[#C3006B]">— {{ $review['rating'] }}/5</span>
                            @endif
                        </p>
                        <p class="text-sm font-jost-regular text-dark-gray-500 dark:text-gray-200">
                            {{ $review['comment'] ?? '' }}
                        </p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if(count($site->scheduleDays()) > 0)
        <section class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 pb-12 sm:pb-16">
            <h2 class="text-2xl sm:text-3xl font-jost-bold text-[#C20A6C] text-center mb-8">ГРАФИК РАБОТЫ</h2>
            <div class="space-y-3">
                @foreach($site->scheduleDays() as $day)
                    <div class="flex flex-col sm:flex-row sm:justify-between border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-4 py-3">
                        <span class="font-jost-bold text-dark-blue-500 dark:text-dark-blue-300">{{ $day['name'] ?? '' }}</span>
                        @if(!empty($day['is_day_off']))
                            <span class="font-jost-regular text-dark-gray-500 dark:text-gray-300">{{ $day['day_off_text'] ?? 'Выходной' }}</span>
                        @else
                            <span class="font-jost-regular text-dark-gray-500 dark:text-gray-300">
                                Цех: {{ $day['workshop'] ?? '—' }} · Доставка: {{ $day['delivery'] ?? '—' }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ url('/work-schedule') }}" class="font-jost-bold text-[#C3006B] underline">Полный график</a>
            </div>
        </section>
    @endif

    @if(count($site->faqItems()) > 0)
        <section class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 pb-12 sm:pb-16 lg:pb-20">
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-[#C20A6C] text-center mb-8 sm:mb-10">
                ЧАСТЫЕ ВОПРОСЫ
            </h2>
            <div class="space-y-4">
                @foreach($site->faqItems() as $item)
                    <details class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 bg-white/80 dark:bg-transparent" @if($loop->first) open @endif>
                        <summary class="cursor-pointer px-4 sm:px-6 py-4 text-base sm:text-lg font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 list-none flex justify-between gap-4">
                            <span>{{ $item['question'] ?? '' }}</span>
                            <span class="text-pink-500">+</span>
                        </summary>
                        <div class="px-4 sm:px-6 pb-4 text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200">
                            @foreach(($item['answer_lines'] ?? []) as $line)
                                <p class="mb-2">{{ $line }}</p>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    @include('public.partials.order-cta')
</div>
@endsection
