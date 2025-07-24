@props(['navigation' => []])

<!-- Навигация -->
<nav class="navbar">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-20">
            <!-- Лого -->
            <a href="#" class="flex items-center">
                <img src="/logo.png" alt="Заточка ТСК" class="nav-logo">
            </a>

            <!-- Десктопное меню -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="#about" class="nav-link">О нас</a>
                <a href="#services" class="nav-link">Услуги</a>
                <a href="#advantages" class="nav-link">Преимущества</a>
                <a href="#contacts" class="nav-link">Контакты</a>
                <button class="btn-primary">Заточить инструменты</button>
            </div>

            <!-- Мобильная кнопка -->
            <button class="mobile-menu-btn md:hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Мобильное меню -->
        <div class="nav-menu md:hidden">
            <div class="flex flex-col space-y-4">
                <a href="#about" class="nav-link">О нас</a>
                <a href="#services" class="nav-link">Услуги</a>
                <a href="#advantages" class="nav-link">Преимущества</a>
                <a href="#contacts" class="nav-link">Контакты</a>
                <button class="btn-primary w-full">Заточить инструменты</button>
            </div>
        </div>
    </div>
</nav>
