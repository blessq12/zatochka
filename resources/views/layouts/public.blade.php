<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Заточка.ТСК' }}</title>
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/public.js'])
</head>
<body data-page="{{ $page ?? 'home' }}">
    <div id="app"></div>
</body>
</html>
