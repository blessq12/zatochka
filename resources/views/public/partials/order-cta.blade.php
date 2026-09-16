@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<section class="bg-white dark:bg-dark-blue-500 py-8">
    <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/sharpening#order') }}"
               class="bg-dark-blue-500 hover:bg-dark-blue-600 text-white text-center px-8 py-4 font-jost-bold text-lg">
                ЗАКАЗАТЬ ЗАТОЧКУ
            </a>
            <a href="{{ url('/repair#order') }}"
               class="bg-pink-500 hover:bg-pink-600 text-white text-center px-8 py-4 font-jost-bold text-lg">
                ЗАКАЗАТЬ РЕМОНТ
            </a>
        </div>
    </div>
</section>
