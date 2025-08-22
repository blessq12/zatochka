<script>
import ReviewModal from "./modals/ReviewModal.vue";

export default {
    name: "Reviews",
    components: {
        ReviewModal,
    },
    props: {
        entityType: {
            type: String,
            default: "App\\Models\\Company", // По умолчанию отзывы о компании
        },
        entityId: {
            type: Number,
            default: 1, // ID компании по умолчанию
        },
        type: {
            type: String,
            default: "testimonial", // Тип отзыва
        },
    },
    data() {
        return {
            reviews: [],
            stats: {
                total: 0,
                averageRating: 0,
                positivePercentage: 0,
                recentCount: 0,
            },
            showReviewModal: false,
            loading: false,
            page: 1,
            hasMoreReviews: true,
        };
    },
    mounted() {
        this.loadReviews();
        this.loadStats();
    },
    methods: {
        async loadReviews() {
            this.loading = true;
            try {
                const response = await fetch(
                    `/api/reviews?type=${this.type}&entity_type=${this.entityType}&entity_id=${this.entityId}&page=${this.page}`
                );
                const data = await response.json();

                if (data.success) {
                    if (this.page === 1) {
                        this.reviews = data.data.data;
                    } else {
                        this.reviews.push(...data.data.data);
                    }

                    this.hasMoreReviews = data.data.next_page_url !== null;
                }
            } catch (error) {
                console.error("Ошибка загрузки отзывов:", error);
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                const response = await fetch(
                    `/api/reviews/stats?type=${this.type}&entity_type=${this.entityType}&entity_id=${this.entityId}`
                );
                const data = await response.json();

                if (data.success) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error("Ошибка загрузки статистики:", error);
            }
        },

        handleReviewSubmitted(review) {
            // Перезагружаем отзывы и статистику
            this.page = 1;
            this.loadReviews();
            this.loadStats();
        },

        loadMoreReviews() {
            this.page++;
            this.loadReviews();
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString("ru-RU", {
                year: "numeric",
                month: "long",
                day: "numeric",
            });
        },

        showNotification(message, type = "info") {
            alert(message);
        },
    },
};
</script>

<template>
    <div class="reviews-section">
        <!-- Заголовок секции -->
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-4 dark:text-white">
                Отзывы наших клиентов
            </h2>
            <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Узнайте, что говорят о нас клиенты, и поделитесь своим опытом
            </p>
        </div>

        <!-- Статистика отзывов -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg p-6 text-center shadow-lg"
            >
                <div class="text-3xl font-bold text-accent mb-2">
                    {{ stats.total }}
                </div>
                <div class="text-gray-600 dark:text-gray-400">
                    Всего отзывов
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg p-6 text-center shadow-lg"
            >
                <div class="text-3xl font-bold text-accent mb-2">
                    {{ stats.averageRating.toFixed(1) }}
                </div>
                <div class="text-gray-600 dark:text-gray-400">
                    Средний рейтинг
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg p-6 text-center shadow-lg"
            >
                <div class="text-3xl font-bold text-accent mb-2">
                    {{ stats.positivePercentage }}%
                </div>
                <div class="text-gray-600 dark:text-gray-400">
                    Положительных
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg p-6 text-center shadow-lg"
            >
                <div class="text-3xl font-bold text-accent mb-2">
                    {{ stats.recentCount }}
                </div>
                <div class="text-gray-600 dark:text-gray-400">
                    За этот месяц
                </div>
            </div>
        </div>

        <!-- Список отзывов -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <div
                v-for="review in reviews"
                :key="review.id"
                class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg hover:shadow-xl transition-shadow duration-300"
            >
                <!-- Рейтинг -->
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400 mr-3">
                        <i
                            v-for="star in 5"
                            :key="star"
                            :class="[
                                'mdi',
                                star <= review.rating
                                    ? 'mdi-star'
                                    : 'mdi-star-outline',
                                'text-lg',
                            ]"
                        ></i>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400"
                        >{{ review.rating }}/5</span
                    >
                </div>

                <!-- Комментарий -->
                <p
                    class="text-gray-700 dark:text-gray-300 mb-4 leading-relaxed"
                >
                    "{{ review.comment }}"
                </p>

                <!-- Информация об авторе -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div
                            class="w-10 h-10 bg-accent/10 rounded-full flex items-center justify-center mr-3"
                        >
                            <i class="mdi mdi-account text-accent"></i>
                        </div>
                        <div>
                            <div
                                class="font-semibold text-gray-900 dark:text-white"
                            >
                                {{ review.user?.name || "Анонимный клиент" }}
                            </div>
                            <div
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ formatDate(review.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ответ на отзыв -->
                <div
                    v-if="review.reply"
                    class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                >
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <i class="mdi mdi-reply mr-1"></i>
                        Ответ компании:
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 text-sm">
                        {{ review.reply }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Кнопка "Показать больше" -->
        <div v-if="hasMoreReviews" class="text-center mb-12">
            <button
                @click="loadMoreReviews"
                :disabled="loading"
                class="inline-flex items-center px-6 py-3 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg transition-colors duration-300 disabled:opacity-50"
            >
                <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                <span v-else>Показать больше отзывов</span>
            </button>
        </div>

        <div class="text-center">
            <button
                @click="showReviewModal = true"
                class="inline-flex items-center px-8 py-3 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg transition-colors duration-300"
            >
                <i class="mdi mdi-plus mr-2"></i>
                Оставить отзыв
            </button>
        </div>

        <!-- Модальное окно для отзыва -->
        <review-modal
            :show="showReviewModal"
            :entity-type="entityType"
            :entity-id="entityId"
            :type="type"
            size="lg"
            @close="showReviewModal = false"
            @submitted="handleReviewSubmitted"
        />
    </div>
</template>

<style scoped>
/* Анимации для звезд */
.text-yellow-400 {
    animation: starGlow 0.3s ease-in-out;
}

@keyframes starGlow {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

/* Hover эффекты для карточек отзывов */
.reviews-section .bg-white:hover,
.reviews-section .bg-gray-800:hover {
    transform: translateY(-2px);
    transition: transform 0.3s ease;
}
</style>
