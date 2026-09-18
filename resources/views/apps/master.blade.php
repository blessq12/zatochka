<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'POS — Заточка.ТСК' }}</title>
    @include('apps.partials.pwa-meta', [
        'manifestUrl' => url('/pwa/master/manifest.webmanifest'),
        'pwaName' => 'Заточка — Мастер',
        'appleTitle' => 'Мастер',
        'themeColor' => '#ffffff',
        'statusBarStyle' => 'default',
        'appleTouchIcon' => 'pwa/icons/master/apple-touch-icon.png',
    ])
    @vite(['resources/css/apps.css', 'resources/js/apps/master/main.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
