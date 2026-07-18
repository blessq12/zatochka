<script>
export default {
    name: "ClientReviewsCarousel",
    props: {
        items: {
            type: Array,
            default: () => [],
        },
        averageRating: {
            type: String,
            default: null,
        },
    },
    methods: {
        stars(rating) {
            const value = Math.max(0, Math.min(5, Number(rating) || 0));
            return "★".repeat(value) + "☆".repeat(5 - value);
        },
        scrollBy(direction) {
            const track = this.$refs.track;
            if (!track) {
                return;
            }

            const amount = Math.max(260, Math.floor(track.clientWidth * 0.85));
            track.scrollBy({ left: direction * amount, behavior: "smooth" });
        },
    },
};
</script>

<template>
    <section
        class="bg-white/80 backdrop-blur-xl text-dark-gray-500 dark:bg-dark-blue-500/90 dark:backdrop-blur-xl dark:text-gray-100"
    >
        <div
            class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 pb-12 sm:pb-16 lg:pb-20"
        >
            <div class="text-center mb-8 sm:mb-10">
                <h2
                    class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C]"
                >
                    ОТЗЫВЫ КЛИЕНТОВ
                </h2>
                <p
                    v-if="averageRating"
                    class="mt-2 text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300"
                >
                    Средняя оценка {{ averageRating }} из 5
                </p>
            </div>

            <div
                v-if="items.length > 1"
                class="flex items-center justify-end gap-2 mb-4"
            >
                <button
                    type="button"
                    class="w-10 h-10 border border-dark-blue-500/40 dark:border-dark-gray-200/80 text-dark-blue-500 dark:text-white hover:bg-dark-blue-500 hover:text-white transition-colors"
                    aria-label="Предыдущий отзыв"
                    @click="scrollBy(-1)"
                >
                    ←
                </button>
                <button
                    type="button"
                    class="w-10 h-10 border border-dark-blue-500/40 dark:border-dark-gray-200/80 text-dark-blue-500 dark:text-white hover:bg-dark-blue-500 hover:text-white transition-colors"
                    aria-label="Следующий отзыв"
                    @click="scrollBy(1)"
                >
                    →
                </button>
            </div>

            <div
                v-if="!items.length"
                class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 p-6 text-center text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300"
            >
                Пока нет опубликованных отзывов.
            </div>

            <div
                v-else
                ref="track"
                class="reviews-track flex gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-2"
            >
                <article
                    v-for="item in items"
                    :key="item.id"
                    class="reviews-card snap-start shrink-0 w-[85%] sm:w-[70%] md:w-[48%] border border-dark-blue-500/30 dark:border-dark-gray-200/90 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl p-5 sm:p-6"
                >
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <p
                            class="text-sm sm:text-base font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                        >
                            {{ item.client_name }}
                        </p>
                        <p
                            class="text-sm sm:text-base font-jost-bold text-[#C20A6C] tracking-wider"
                            :aria-label="`Оценка ${item.rating} из 5`"
                        >
                            {{ stars(item.rating) }}
                        </p>
                    </div>

                    <p
                        class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200 whitespace-pre-line"
                    >
                        {{ item.comment }}
                    </p>

                    <div
                        v-if="item.manager_reply"
                        class="mt-4 pt-4 border-t border-dark-blue-500/20 dark:border-dark-gray-200/40"
                    >
                        <p
                            class="text-xs sm:text-sm font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-1"
                        >
                            Ответ мастерской
                        </p>
                        <p
                            class="text-xs sm:text-sm font-jost-regular text-dark-gray-500 dark:text-gray-300 whitespace-pre-line"
                        >
                            {{ item.manager_reply }}
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
.reviews-track {
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch;
}

.reviews-track::-webkit-scrollbar {
    height: 6px;
}

.reviews-track::-webkit-scrollbar-thumb {
    background: rgba(0, 56, 89, 0.35);
}
</style>
