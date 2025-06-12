<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <meta name="description" content="">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <main id="app">
        <div class="mx-auto max-w-7xl px-4 md:px-6 py-12">
            <div class="bg-gray-700 p-12 rounded-lg">
                <h1 class="text-white text-4xl font-bold">
                    Hello World
                </h1>
            </div>
        </div>
    </main>
</body>

</html>
