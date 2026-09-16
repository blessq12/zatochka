@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
    $navClass = 'text-white text-xs xl:text-sm font-jost-medium px-3 xl:px-4 py-2 rounded-xl hover:bg-white/20 transition-all duration-300 whitespace-nowrap';
    $navActive = 'bg-white/30 font-jost-bold';
@endphp
<nav class="bg-[#C20A6C] sticky top-0 z-50" data-site-nav>
    <div class="container mx-auto">
        <div class="flex justify-between items-center py-4 h-20 px-6 sm:px-8 lg:px-12">
            <a href="{{ url('/') }}" class="flex items-center space-x-2 sm:space-x-3 group flex-shrink-0">
                <span class="flex flex-col">
                    <span class="text-[10px] font-jost-regular text-white leading-tight">ОСНОВАНО 2020</span>
                    <span class="text-lg sm:text-xl font-jost-bold text-white leading-tight">
                        ЗАТОЧКА<span class="text-[#003859]">.</span>ТСК
                    </span>
                    <span class="text-[10px] sm:text-xs font-jost-regular text-white leading-tight">
                        ПОРА ЗАТОЧИТЬ ИНСТРУМЕНТЫ
                    </span>
                </span>
            </a>

            <div class="hidden lg:flex items-center space-x-1 xl:space-x-2 flex-1 justify-center mx-8">
                <a href="{{ url('/') }}" class="{{ $navClass }} {{ $site->isActive('/') ? $navActive : '' }}">ГЛАВНАЯ</a>
                <a href="{{ url('/sharpening') }}" class="{{ $navClass }} {{ $site->isActive('/sharpening') ? $navActive : '' }}">ЗАТОЧКА</a>
                <a href="{{ url('/repair') }}" class="{{ $navClass }} {{ $site->isActive('/repair') ? $navActive : '' }}">РЕМОНТ</a>
                <a href="{{ url('/delivery') }}" class="{{ $navClass }} {{ $site->isActive('/delivery') ? $navActive : '' }}">ДОСТАВКА</a>
                <a href="{{ url('/prices') }}" class="{{ $navClass }} {{ $site->isActive('/prices') ? $navActive : '' }}">ПРАЙС</a>
                <a href="{{ url('/work-schedule') }}" class="{{ $navClass }} {{ $site->isActive('/work-schedule') ? $navActive : '' }}">ГРАФИК</a>
                <a href="{{ url('/contacts') }}" class="{{ $navClass }} {{ $site->isActive('/contacts') ? $navActive : '' }}">КОНТАКТЫ</a>
            </div>

            <div class="hidden lg:flex items-center space-x-3 flex-shrink-0">
                <a href="{{ url('/client/dashboard') }}"
                   class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 font-jost-bold text-sm transition-all duration-300">
                    ВОЙТИ
                </a>
            </div>

            <button type="button"
                    class="flex lg:hidden w-10 h-10 flex-col justify-center items-center space-y-1.5"
                    data-mobile-menu-toggle
                    aria-label="Меню"
                    aria-expanded="false">
                <span class="block w-6 h-0.5 bg-black"></span>
                <span class="block w-6 h-0.5 bg-black"></span>
                <span class="block w-6 h-0.5 bg-black"></span>
            </button>
        </div>
    </div>

    <div class="lg:hidden hidden border-t border-white/20 bg-[#C20A6C]" data-mobile-menu>
        <div class="px-6 py-4 space-y-2">
            <a href="{{ url('/') }}" class="block text-white font-jost-medium py-2">ГЛАВНАЯ</a>
            <a href="{{ url('/sharpening') }}" class="block text-white font-jost-medium py-2">ЗАТОЧКА</a>
            <a href="{{ url('/repair') }}" class="block text-white font-jost-medium py-2">РЕМОНТ</a>
            <a href="{{ url('/delivery') }}" class="block text-white font-jost-medium py-2">ДОСТАВКА</a>
            <a href="{{ url('/prices') }}" class="block text-white font-jost-medium py-2">ПРАЙС</a>
            <a href="{{ url('/work-schedule') }}" class="block text-white font-jost-medium py-2">ГРАФИК</a>
            <a href="{{ url('/contacts') }}" class="block text-white font-jost-medium py-2">КОНТАКТЫ</a>
            <a href="{{ url('/client/dashboard') }}" class="block text-white font-jost-bold py-2">ВОЙТИ</a>
        </div>
    </div>
</nav>
