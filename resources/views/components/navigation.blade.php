@props([
    'navigation' => [
        [
            'name' => 'Заточка инструмента',
            'href' => '/sharpening',
        ],
        [
            'name' => 'Ремонт инструмента',
            'href' => '/repair',
        ],
        [
            'name' => 'Доставка',
            'href' => '/delivery',
        ],
        [
            'name' => 'Контакты',
            'href' => '/contacts',
        ],
    ],
])

<!-- Навигация -->
<nav class="navbar shadow-md">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center w-full h-20 ">
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="/logo.png" alt="Заточка ТСК" class="nav-logo">
            </a>

            <div class="hidden md:flex items-center space-x-4 justify-end w-full">
                @foreach ($navigation as $item)
                    <a href="{{ $item['href'] }}" class="nav-link group">
                        <span class="flex items-center">

                            {{ $item['name'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <mobile-menu :navigation='@json($navigation)' :company='@json($company)'>
            </mobile-menu>
        </div>
    </div>
</nav>
