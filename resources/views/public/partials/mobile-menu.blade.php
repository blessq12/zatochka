@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<div class="lg:hidden hidden fixed inset-0 bg-[#003859] z-[100] flex-col" data-mobile-menu>
    <div class="flex justify-between items-center px-6 sm:px-8 lg:px-12 py-4 h-20">
        <a href="{{ url('/') }}" class="flex items-center space-x-2 sm:space-x-3 p-2 -m-2 flex-shrink-0">
            <span class="text-lg font-jost-bold text-white">ЗАТОЧКА<span class="text-[#C20A6C]">.</span>ТСК</span>
        </a>
        <div class="flex items-center space-x-3">
            <a href="{{ url('/client/dashboard') }}" class="bg-[#C20A6C] text-white px-4 py-2 rounded-xl font-jost-bold text-sm">ВОЙТИ</a>
            <button type="button" data-mobile-menu-toggle class="w-10 h-10 text-white text-2xl" aria-label="Закрыть">×</button>
        </div>
    </div>
    <div class="flex-1 px-6 py-8 space-y-4 overflow-y-auto">
        <a href="{{ url('/') }}" class="block text-white font-jost-medium text-xl py-2">ГЛАВНАЯ</a>
        <a href="{{ url('/sharpening') }}" class="block text-white font-jost-medium text-xl py-2">ЗАТОЧКА</a>
        <a href="{{ url('/repair') }}" class="block text-white font-jost-medium text-xl py-2">РЕМОНТ</a>
        <a href="{{ url('/delivery') }}" class="block text-white font-jost-medium text-xl py-2">ДОСТАВКА</a>
        <a href="{{ url('/prices') }}" class="block text-white font-jost-medium text-xl py-2">ПРАЙС</a>
        <a href="{{ url('/work-schedule') }}" class="block text-white font-jost-medium text-xl py-2">ГРАФИК</a>
        <a href="{{ url('/contacts') }}" class="block text-white font-jost-medium text-xl py-2">КОНТАКТЫ</a>
    </div>
</div>
