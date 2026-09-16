@extends('layouts.site')

@section('meta_description', 'График работы цеха и доставки Заточка.ТСК.')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    @include('public.partials.page-hero', ['title' => 'ГРАФИК РАБОТЫ'])

    <section class="py-12 sm:py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-4">
            @forelse($site->scheduleDays() as $day)
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
            @empty
                <p class="text-center text-dark-gray-500 dark:text-gray-300">График временно недоступен.</p>
            @endforelse
        </div>
    </section>

    @include('public.partials.order-cta')
</div>
@endsection
