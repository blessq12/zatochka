<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="client-pwa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Личный кабинет — Заточка.ТСК' }}</title>
    @include('apps.partials.pwa-meta', [
        'manifestUrl' => url('/pwa/client/manifest.webmanifest'),
        'pwaName' => 'Заточка — Клиент',
        'appleTitle' => 'Клиент',
        'themeColor' => '#C20A6C',
        'statusBarStyle' => 'black-translucent',
        'appleTouchIcon' => 'pwa/icons/client/apple-touch-icon.png',
    ])
    <meta name="theme-color" content="#C20A6C" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#C20A6C" media="(prefers-color-scheme: dark)">
    @vite(['resources/css/apps.css', 'resources/js/apps/client/main.js'])
</head>
<body class="client-app-root">
    <div id="app"></div>
</body>
</html>
