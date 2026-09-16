<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $site->title ?? ($title ?? 'Заточка.ТСК') }}</title>
    <meta name="description" content="@yield('meta_description', 'Профессиональная заточка инструментов в Томске. Заточка.ТСК.')">
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
