<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $site->title }}</title>
    <meta name="description" content="@yield('meta_description', 'Профессиональная заточка маникюрных, парикмахерских и грумерских инструментов в Томске. Заточка.ТСК.')">
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/public/site.js'])
    @stack('vite')
</head>
<body class="min-h-screen bg-gray-50 dark:bg-dark-blue-500 flex flex-col">
    @include('public.partials.nav')
    <main class="container mx-auto flex-1">
        @yield('content')
    </main>
    @include('public.partials.footer')
</body>
</html>
