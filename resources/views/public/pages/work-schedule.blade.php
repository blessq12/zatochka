@extends('layouts.site')

@section('meta_description', 'График работы цеха и доставки Заточка.ТСК.')

@section('content')
@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<div class="min-h-screen bg-white dark:bg-dark-blue-500">
    @include('public.partials.page-hero', ['title' => 'ГРАФИК РАБОТЫ'])
    @include('public.partials.home.schedule')
</div>
@endsection
