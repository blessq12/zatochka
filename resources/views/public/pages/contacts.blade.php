@extends('layouts.site')

@section('meta_description', 'Контакты мастерской Заточка.ТСК в Томске: телефон, адрес, мессенджеры.')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
    $contacts = $site->contacts();
    $address = $contacts['address'] ?? [];
@endphp
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    <section class="relative overflow-hidden bg-white dark:bg-dark-blue-500 pt-12 sm:pt-16 lg:pt-0 pb-16 sm:pb-20 lg:pb-24">
        <div class="absolute w-200 h-200 sm:w-200 sm:h-200 lg:hidden bg-dark-blue-500 dark:bg-white bottom-0 left-1/2 -translate-x-1/2 rounded-full"></div>
        <div class="hidden lg:block absolute -top-[30%] left-1/2 -translate-x-1/2 -translate-y-[30%] w-[800px] h-[800px] max-w-[800px] max-h-[800px] bg-dark-blue-500 dark:bg-white rounded-full"></div>
        <div class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 relative z-10">
            <div class="lg:pt-12 xl:pt-16 lg:flex lg:flex-col lg:items-center lg:justify-center lg:h-[400px] lg:max-h-[400px]">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-jost-bold dark:text-dark-blue-500 text-white lg:text-white dark:lg:text-dark-blue-500 text-center mb-8 sm:mb-12 lg:max-w-[600px] xl:max-w-[700px] mx-auto px-4">
                    НАШИ КОНТАКТЫ
                </h1>
                <div class="text-center space-y-4">
                    @if(!empty($contacts['contact_person']))
                        <p class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-white dark:text-white lg:text-white dark:lg:text-dark-blue-500">
                            {{ $contacts['contact_person'] }}
                        </p>
                    @endif
                    @if($site->phoneTel() !== '')
                        <a href="tel:{{ $site->phoneTel() }}" class="inline-block text-xl sm:text-2xl lg:text-3xl font-jost-regular text-white underline decoration-white/40 hover:decoration-white lg:text-white dark:lg:text-dark-blue-500">
                            {{ $site->phone() }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
        <div class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
            <div class="flex items-center justify-center space-x-3 mb-6 sm:mb-8">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-pink-500 uppercase">АДРЕС</h2>
            </div>
            @if(!empty($address['main']))
                <p class="text-xl sm:text-2xl lg:text-3xl font-jost-bold text-dark-blue-500 dark:text-white mb-4 sm:mb-6 text-center">
                    {{ $address['main'] }}
                </p>
            @endif
            @if(!empty($address['directions']))
                <p class="text-base sm:text-lg lg:text-xl font-jost-regular text-dark-gray-500 dark:text-gray-200 whitespace-pre-line text-center mb-8">
                    {{ $address['directions'] }}
                </p>
            @endif
            @if(!empty($contacts['email']))
                <p class="text-center mb-8">
                    <a href="mailto:{{ $contacts['email'] }}" class="font-jost-bold text-dark-blue-500 dark:text-blue-400 underline">
                        {{ $contacts['email'] }}
                    </a>
                </p>
            @endif
            @if(count($site->socialLinks()) > 0)
                <div class="flex justify-center flex-wrap gap-4">
                    @foreach($site->socialLinks() as $link)
                        <a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener" class="font-jost-medium text-dark-blue-500 dark:text-blue-400 underline">
                            {{ $link['name'] ?? '' }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="pb-12 sm:pb-16">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
            @include('public.partials.order-cta')
        </div>
    </section>
</div>
@endsection
