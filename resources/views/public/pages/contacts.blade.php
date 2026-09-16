@extends('layouts.site')

@section('meta_description', 'Контакты мастерской Заточка.ТСК в Томске: телефон, адрес, мессенджеры.')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
    $contacts = $site->contacts();
    $address = $contacts['address'] ?? [];
@endphp
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    <section class="bg-[#C20A6C] text-white py-12 sm:py-16">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 text-center space-y-4">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold tracking-wide">НАШИ КОНТАКТЫ</h1>
            @if(!empty($contacts['contact_person']))
                <p class="text-2xl sm:text-3xl font-jost-bold">{{ $contacts['contact_person'] }}</p>
            @endif
            @if($site->phoneTel() !== '')
                <a href="tel:{{ $site->phoneTel() }}" class="inline-block text-xl sm:text-2xl font-jost-regular underline">
                    {{ $site->phone() }}
                </a>
            @endif
        </div>
    </section>

    <section class="py-12 sm:py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-8">
            <h2 class="text-xl sm:text-2xl font-jost-bold text-[#C3006B] text-center">АДРЕС</h2>
            @if(!empty($address['main']))
                <p class="text-center text-base sm:text-lg font-jost-regular text-dark-gray-500 dark:text-gray-200">
                    {{ $address['main'] }}
                </p>
            @endif
            @if(!empty($address['directions']))
                <p class="text-center text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300">
                    {{ $address['directions'] }}
                </p>
            @endif

            @if(!empty($contacts['email']))
                <p class="text-center">
                    <a href="mailto:{{ $contacts['email'] }}" class="font-jost-bold text-dark-blue-500 dark:text-blue-400 underline">
                        {{ $contacts['email'] }}
                    </a>
                </p>
            @endif

            @if(count($site->socialLinks()) > 0)
                <div class="flex justify-center flex-wrap gap-4">
                    @foreach($site->socialLinks() as $link)
                        <a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener"
                           class="font-jost-medium text-dark-blue-500 dark:text-blue-400 underline">
                            {{ $link['name'] ?? '' }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @include('public.partials.order-cta')
</div>
@endsection
