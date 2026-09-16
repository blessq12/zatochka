@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
@endphp
<section class="bg-white/80 backdrop-blur-xl text-dark-gray-500 dark:bg-dark-blue-500/90 dark:backdrop-blur-xl dark:text-gray-100">
    <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 pb-12 sm:pb-16 lg:pb-20">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C] text-center mb-8 sm:mb-10">
            ЧАСТЫЕ ВОПРОСЫ
        </h2>

        <div class="space-y-4" data-faq-list>
            @forelse($site->faqItems() as $index => $item)
                <div class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 overflow-hidden bg-white/80 backdrop-blur-xl dark:bg-transparent" data-faq-item>
                    <button
                        type="button"
                        class="w-full flex items-center justify-between px-4 sm:px-6 py-4 text-left hover:bg-white/90 dark:hover:bg-gray-900/20 transition-colors duration-200"
                        data-faq-toggle
                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                    >
                        <span class="text-base sm:text-lg font-jost-bold text-dark-blue-500 dark:text-dark-blue-300">
                            {{ $item['question'] ?? '' }}
                        </span>
                        <span class="w-8 h-8 flex items-center justify-center border border-pink-500 dark:border-pink-600 text-pink-500 dark:text-pink-300 text-xl font-jost-bold" data-faq-icon>
                            {{ $index === 0 ? '−' : '+' }}
                        </span>
                    </button>
                    <div
                        class="px-4 sm:px-6 pb-4 text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200{{ $index === 0 ? '' : ' hidden' }}"
                        data-faq-panel
                    >
                        @foreach(($item['answer_lines'] ?? []) as $line)
                            <p class="mb-2">{{ $line }}</p>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center font-jost-regular text-dark-gray-500 dark:text-gray-300">Раздел скоро появится</p>
            @endforelse
        </div>
    </div>
</section>
