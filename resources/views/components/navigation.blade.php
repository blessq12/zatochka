@props([
    'navigation' => [
        [
            'name' => 'О нас',
            'href' => '#about',
        ],
        [
            'name' => 'Услуги',
            'href' => '#services',
        ],
        [
            'name' => 'Преимущества',
            'href' => '#advantages',
        ],
        [
            'name' => 'Контакты',
            'href' => '#contacts',
        ],
    ],
])

<!-- Навигация -->
<nav class="navbar">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-20">
            <a href="#" class="flex items-center">
                <img src="/logo.png" alt="Заточка ТСК" class="nav-logo">
            </a>

            <div class="hidden md:flex items-center space-x-8">
                @foreach ($navigation as $item)
                    <a href="{{ $item['href'] }}" class="nav-link">{{ $item['name'] }}</a>
                @endforeach
                <button class="btn-primary">Заточить инструменты</button>
            </div>


            <mobile-menu :navigation='@json($navigation)' :company='@json($company)'>
            </mobile-menu>
        </div>


    </div>
</nav>
