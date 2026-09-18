<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = (string) (($company['name'] ?? null) ?: 'Заточка.ТСК');
        $siteTagline = (string) (($company['tagline'] ?? null) ?: 'Профессиональная заточка инструментов');
    @endphp
    <title>{{ $title ?? $siteName }}</title>
    <meta name="description" content="@yield('meta_description', $siteTagline.' — '.$siteName.'.')">
    @stack('head')
    @vite(['resources/site/css/site.css', 'resources/site/js/site.js'])
    @stack('vite')
</head>
<body class="min-h-screen bg-gray-50 dark:bg-dark-blue-500 flex flex-col site-root">
    @include('public.partials.nav')
    <main class="container mx-auto flex-1 w-full">
        @yield('content')
    </main>
    @include('public.partials.footer')
</body>
</html>
