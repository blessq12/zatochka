@extends('layouts.site')

@section('meta_description', ($site->document['title'] ?? 'Документ').' — Заточка.ТСК')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
    $document = $site->document ?? [];
@endphp
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    <x-public.page-hero title="{{ $document['title'] ?? 'Документ' }}" />
    <section class="py-12 sm:py-16 lg:py-20">
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 prose dark:prose-invert max-w-none font-jost-regular text-dark-gray-500 dark:text-gray-200">
            {!! $document['body_html'] ?? '' !!}
        </div>
    </section>
</div>
@endsection
