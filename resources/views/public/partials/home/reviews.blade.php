@php
    /** @var \App\Http\ViewModels\PublicSite\PublicSiteViewModel $site */
    $reviews = $site->reviews;
    $items = $reviews['items'] ?? [];
    $averageRating = $reviews['average_rating'] ?? null;
@endphp
<section class="bg-white/80 backdrop-blur-xl text-dark-gray-500 dark:bg-dark-blue-500/90 dark:backdrop-blur-xl dark:text-gray-100">
    <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 pb-12 sm:pb-16 lg:pb-20">
        <div class="text-center mb-8 sm:mb-10">
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C]">
                ОТЗЫВЫ КЛИЕНТОВ
            </h2>
            @if($averageRating)
                <p class="mt-2 text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300">
                    Средняя оценка {{ $averageRating }} из 5
                </p>
            @endif
        </div>

        @if(count($items) > 1)
            <div class="flex items-center justify-end gap-2 mb-4">
                <button
                    type="button"
                    class="w-10 h-10 border border-dark-blue-500/40 dark:border-dark-gray-200/80 text-dark-blue-500 dark:text-white hover:bg-dark-blue-500 hover:text-white transition-colors"
                    aria-label="Предыдущий отзыв"
                    data-reviews-prev
                >
                    ←
                </button>
                <button
                    type="button"
                    class="w-10 h-10 border border-dark-blue-500/40 dark:border-dark-gray-200/80 text-dark-blue-500 dark:text-white hover:bg-dark-blue-500 hover:text-white transition-colors"
                    aria-label="Следующий отзыв"
                    data-reviews-next
                >
                    →
                </button>
            </div>
        @endif

        @if(count($items) === 0)
            <div class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 p-6 text-center text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300">
                Пока нет опубликованных отзывов.
            </div>
        @else
            <div class="reviews-track flex gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-2" data-reviews-track>
                @foreach($items as $item)
                    @php
                        $rating = max(0, min(5, (int) ($item['rating'] ?? 0)));
                        $stars = str_repeat('★', $rating).str_repeat('☆', 5 - $rating);
                    @endphp
                    <article class="reviews-card snap-start shrink-0 w-[85%] sm:w-[70%] md:w-[48%] border border-dark-blue-500/30 dark:border-dark-gray-200/90 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl p-5 sm:p-6">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <p class="text-sm sm:text-base font-jost-bold text-dark-blue-500 dark:text-dark-blue-300">
                                {{ $item['client_name'] ?? 'Клиент' }}
                            </p>
                            <p class="text-sm sm:text-base font-jost-bold text-[#C20A6C] tracking-wider" aria-label="Оценка {{ $rating }} из 5">
                                {{ $stars }}
                            </p>
                        </div>

                        <p class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200 whitespace-pre-line">
                            {{ $item['comment'] ?? '' }}
                        </p>

                        @if(!empty($item['manager_reply']))
                            <div class="mt-4 pt-4 border-t border-dark-blue-500/20 dark:border-dark-gray-200/40">
                                <p class="text-xs sm:text-sm font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-1">
                                    Ответ мастерской
                                </p>
                                <p class="text-sm font-jost-regular text-dark-gray-500 dark:text-gray-200 whitespace-pre-line">
                                    {{ $item['manager_reply'] }}
                                </p>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
