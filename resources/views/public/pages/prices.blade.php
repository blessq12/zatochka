@extends('layouts.site')

@section('meta_description', 'Прайс на заточку и ремонт. Заточка.ТСК, Томск.')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    @include('public.partials.page-hero', ['title' => 'ПРАЙС НА УСЛУГИ'])

    <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-10">
            @include('public.partials.order-cta', ['layout' => 'stack'])
        </div>
    </section>

    <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-6">
            <h2 class="text-3xl sm:text-4xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 text-center">
                ПРАЙС НА ЗАТОЧКУ
            </h2>
            @include('public.partials.price-list', ['prices' => $site->sharpeningPrices()])

            <h2 class="text-3xl sm:text-4xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 text-center pt-4">
                ПРАЙС РЕМОНТ АППАРАТОВ
            </h2>
            @include('public.partials.price-list', ['prices' => $site->repairPrices()])
        </div>
    </section>
</div>
@endsection
