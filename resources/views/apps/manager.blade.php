<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="apps-pwa">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Менеджер — Заточка.ТСК' }}</title>
    @include('apps.partials.pwa-meta', [
        'manifestUrl' => url('/pwa/manager/manifest.webmanifest'),
        'pwaName' => 'Заточка — Менеджер',
        'appleTitle' => 'Менеджер',
        'themeColor' => '#003859',
        'appleTouchIcon' => 'pwa/icons/manager/apple-touch-icon.png',
    ])
    @vite(['resources/css/apps.css', 'resources/js/apps/manager/main.js'])
</head>
<body class="apps-pwa-root">
    <div id="app"></div>
</body>
</html>
