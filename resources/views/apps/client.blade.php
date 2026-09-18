<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="apps-pwa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Личный кабинет — Заточка.ТСК' }}</title>
    @include('apps.partials.pwa-meta', [
        'manifestUrl' => url('/pwa/client/manifest.webmanifest'),
        'pwaName' => 'Заточка — Клиент',
        'appleTitle' => 'Клиент',
        'themeColor' => '#C20A6C',
        'appleTouchIcon' => 'pwa/icons/client/apple-touch-icon.png',
    ])
    @vite(['resources/css/apps.css', 'resources/js/apps/client/main.js'])
</head>
<body class="apps-pwa-root client-app-root">
    <div id="app"></div>
</body>
</html>
