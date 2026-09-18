<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Менеджер — Заточка.ТСК' }}</title>
    @include('apps.partials.pwa-meta', [
        'manifestUrl' => url('/pwa/manager/manifest.webmanifest'),
        'pwaName' => 'Заточка — Менеджер',
        'appleTitle' => 'Менеджер',
        'themeColor' => '#003859',
    ])
    @vite(['resources/css/apps.css', 'resources/js/apps/manager/main.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
