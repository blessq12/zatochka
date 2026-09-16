@extends('layouts.site')

@section('meta_description', 'Прайс на заточку и ремонт. Заточка.ТСК, Томск.')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    @include('public.partials.page-hero', ['title' => 'ПРАЙС'])

    <section class="py-12 sm:py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-12">
            <div>
                <h2 class="text-xl sm:text-2xl font-jost-bold text-[#C3006B] text-center mb-6">ЗАТОЧКА</h2>
                @include('public.partials.price-list', ['prices' => $site->sharpeningPrices()])
                <div class="mt-6 text-center">
                    <a href="{{ url('/sharpening#order') }}" class="font-jost-bold text-[#C3006B] underline">Заказать заточку</a>
                </div>
            </div>
            <div>
                <h2 class="text-xl sm:text-2xl font-jost-bold text-[#C3006B] text-center mb-6">РЕМОНТ</h2>
                @include('public.partials.price-list', ['prices' => $site->repairPrices()])
                <div class="mt-6 text-center">
                    <a href="{{ url('/repair#order') }}" class="font-jost-bold text-[#C3006B] underline">Заказать ремонт</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
