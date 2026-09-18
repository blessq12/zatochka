<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Личный кабинет — Заточка.ТСК' }}</title>
    @include('apps.partials.pwa-meta', [
        'manifestUrl' => url('/pwa/client/manifest.webmanifest'),
        'pwaName' => 'Заточка — Клиент',
        'appleTitle' => 'Клиент',
        'themeColor' => '#ffffff',
        'statusBarStyle' => 'default',
        'appleTouchIcon' => 'pwa/icons/client/apple-touch-icon.png',
    ])
    @vite(['resources/css/apps.css', 'resources/js/apps/client/main.js'])
</head>
<body class="client-app-root">
    <div id="app"></div>
</body>
</html>
