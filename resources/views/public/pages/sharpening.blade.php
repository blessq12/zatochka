@extends('layouts.site')

@section('meta_description', 'Прайс и заказ профессиональной заточки инструментов. Заточка.ТСК, Томск.')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    @include('public.partials.page-hero', ['title' => 'ПРАЙС НА ЗАТОЧКУ'])

    <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-10">
            <div class="flex justify-center pt-4">
                <a href="#order"
                   class="bg-[#C3006B] hover:bg-[#C3006B]/90 text-white px-10 py-8 w-full font-jost-bold text-lg sm:text-xl text-center">
                    Заказать
                </a>
            </div>
            @include('public.partials.price-list', ['prices' => $site->sharpeningPrices()])
        </div>
    </section>

    <section id="order" class="scroll-mt-24">
        @include('public.partials.forms.sharpening-order', ['toolTypes' => $toolTypes])
    </section>
</div>
@endsection
