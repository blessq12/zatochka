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
        <div class="mx-auto max-w-7xl px-4 md:px-6 h-screen flex items-center justify-center">
            <div class="bg-gray-700 py-4 px-4 rounded-lg flex flex-col items-center justify-center w-fit gap-4">
                <img src="/logo.png" alt="logo" class="w-[50%] h-full">
                <ul class="block space-y-1 md:flex gap-4">
                    <li class="text-gray-200 font-light ">
                        <a href="#">
                            <span class="mdi mdi-map-marker"></span>
                            Карповский 12
                        </a>
                    </li>
                    <li class="text-gray-200 font-light ">
                        <a href="tel:+79832335907">
                            <span class="mdi mdi-phone"></span>
                            +79832335907
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </main>
</body>

</html>
