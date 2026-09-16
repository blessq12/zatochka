@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
    $writeHref = $site->messengerWriteUrl();
@endphp
<div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center mb-8">
    @if($site->phoneTel() !== '')
        <a
            href="tel:{{ $site->phoneTel() }}"
            class="bg-white dark:bg-dark-blue-500 hover:bg-gray-100 dark:hover:bg-dark-blue-400 text-dark-blue-500 dark:text-white border-2 border-dark-blue-500 dark:border-dark-blue-400 px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform text-center"
        >
            ПОЗВОНИТЬ
        </a>
    @endif
    @if($writeHref !== '')
        <a
            href="{{ $writeHref }}"
            target="_blank"
            rel="noopener"
            class="bg-white dark:bg-dark-blue-500 hover:bg-gray-100 dark:hover:bg-dark-blue-400 text-dark-blue-500 dark:text-white border-2 border-dark-blue-500 dark:border-dark-blue-400 px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform text-center"
        >
            НАПИСАТЬ
        </a>
    @endif
</div>
