<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Заточка' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    {{ $head ?? null }}
</head>

<body class="font-sans antialiased">
    <div id="app" class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Navigation -->
        <x-navigation.main />

        <!-- Page Content -->
        <main class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-12 sm:py-16 lg:py-20">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <x-footer.main />
        <callback-widget></callback-widget>
    </div>

    @stack('scripts')
</body>

</html>
