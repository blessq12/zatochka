<script>
import { gsap } from "gsap";
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";
import { useOrderStore } from "../../stores/orderStore.js";

export default {
    name: "OrdersList",
    data() {
        return {
            showReviewSlide: false,
            selectedOrder: null,
            reviewForm: {
                rating: 5,
                comment: "",
            },
        };
    },
    computed: {
        ...mapStores(useOrderStore, useAuthStore),
    },
    methods: {
        getStatusClass(status) {
            switch (status) {
                case "new":
                    return "bg-blue-500/20 text-blue-700 border-blue-600/30 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30";
                case "consultation":
                    return "bg-purple-500/20 text-purple-700 border-purple-600/30 dark:bg-purple-500/20 dark:text-purple-300 dark:border-purple-500/30";
                case "diagnostic":
                    return "bg-orange-500/20 text-orange-700 border-orange-600/30 dark:bg-orange-500/20 dark:text-orange-300 dark:border-orange-500/30";
                case "in_work":
                    return "bg-yellow-500/20 text-yellow-700 border-yellow-600/30 dark:bg-yellow-500/20 dark:text-yellow-300 dark:border-yellow-500/30";
                case "waiting_parts":
                    return "bg-amber-500/20 text-amber-700 border-amber-600/30 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/30";
                case "ready":
                    return "bg-green-500/20 text-green-700 border-green-600/30 dark:bg-green-500/20 dark:text-green-300 dark:border-green-500/30";
                case "issued":
                    return "bg-emerald-500/20 text-emerald-700 border-emerald-600/30 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/30";
                case "cancelled":
                    return "bg-red-500/20 text-red-700 border-red-600/30 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/30";
                default:
                    return "bg-gray-500/20 text-gray-700 border-gray-600/30 dark:bg-gray-500/20 dark:text-gray-300 dark:border-gray-500/30";
            }
        },
        getStatusText(status) {
            const statusMap = {
                new: "Новый",
                consultation: "Консультация",
                diagnostic: "Диагностика",
                in_work: "В работе",
                waiting_parts: "Ожидание запчастей",
                ready: "Готов",
                issued: "Выдан",
                cancelled: "Отменен",
            };
            return statusMap[status] || status || "Неизвестно";
        },
        getTypeText(type) {
            const typeMap = {
                repair: "Ремонт",
                sharpening: "Заточка",
                diagnostic: "Диагностика",
                replacement: "Замена",
                maintenance: "Обслуживание",
                consultation: "Консультация",
                warranty: "Гарантийный",
            };
            return typeMap[type] || type || "Не указан";
        },
        formatPrice(price) {
            if (!price) return "Не указана";
            return new Intl.NumberFormat("ru-RU", {
                style: "currency",
                currency: "RUB",
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }).format(price);
        },
        formatDate(dateString) {
            if (!dateString) return "Не указана";
            const date = new Date(dateString);
            return date.toLocaleDateString("ru-RU", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            });
        },
        async loadPage(page) {
            if (page < 1 || page > this.orderStore.pagination.last_page) return;

            await this.orderStore.getClientOrders(
                this.authStore.token,
                page,
                this.orderStore.pagination.per_page
            );
        },
        getVisiblePages() {
            const current = this.orderStore.pagination.current_page;
            const last = this.orderStore.pagination.last_page;
            const pages = [];

            // Показываем максимум 5 страниц
            let start = Math.max(1, current - 2);
            let end = Math.min(last, current + 2);

            // Если мы в начале, показываем больше страниц справа
            if (current <= 3) {
                end = Math.min(last, 5);
            }

            // Если мы в конце, показываем больше страниц слева
            if (current >= last - 2) {
                start = Math.max(1, last - 4);
            }

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            return pages;
        },
        canLeaveReview(order) {
            return order.status === "issued";
        },
        openReviewSlide(order) {
            this.selectedOrder = order;
            this.reviewForm = {
                rating: 5,
                comment: "",
            };
            this.showReviewSlide = true;

            // Анимация появления через GSAP
            this.$nextTick(() => {
                this.animateReviewSlideIn();
            });
        },
        closeReviewSlide() {
            // Анимация скрытия через GSAP
            this.animateReviewSlideOut(() => {
                this.showReviewSlide = false;
                this.selectedOrder = null;
                this.reviewForm = {
                    rating: 5,
                    comment: "",
                };
            });
        },
        animateReviewSlideIn() {
            const reviewSlide = this.$el.querySelector(".review-slide");
            if (!reviewSlide) return;

            // Устанавливаем начальное состояние для контейнера
            gsap.set(reviewSlide, {
                height: 0,
                opacity: 0,
                y: -20,
                scale: 0.95,
            });

            // Устанавливаем начальное состояние для внутренних элементов
            const header = reviewSlide.querySelector(".review-header");
            const content = reviewSlide.querySelector(".review-content");
            const buttons = reviewSlide.querySelector(".review-buttons");

            gsap.set([header, content, buttons], {
                opacity: 0,
                y: 20,
            });

            // Анимация появления контейнера
            gsap.to(reviewSlide, {
                height: "auto",
                opacity: 1,
                y: 0,
                scale: 1,
                duration: 0.4,
                ease: "power2.out",
                onComplete: () => {
                    // Анимация появления внутренних элементов
                    gsap.to([header, content, buttons], {
                        opacity: 1,
                        y: 0,
                        duration: 0.3,
                        ease: "power2.out",
                        stagger: 0.1,
                    });
                },
            });
        },
        animateReviewSlideOut(callback) {
            const reviewSlide = this.$el.querySelector(".review-slide");
            if (!reviewSlide) {
                callback();
                return;
            }

            // Анимация скрытия внутренних элементов
            const header = reviewSlide.querySelector(".review-header");
            const content = reviewSlide.querySelector(".review-content");
            const buttons = reviewSlide.querySelector(".review-buttons");

            gsap.to([header, content, buttons], {
                opacity: 0,
                y: -20,
                duration: 0.2,
                ease: "power2.in",
                stagger: 0.05,
                onComplete: () => {
                    // Анимация скрытия контейнера
                    gsap.to(reviewSlide, {
                        height: 0,
                        opacity: 0,
                        y: -20,
                        scale: 0.95,
                        duration: 0.3,
                        ease: "power2.in",
                        onComplete: callback,
                    });
                },
            });
        },
        async submitReview() {
            if (!this.selectedOrder || !this.reviewForm.comment.trim()) {
                return;
            }

            const result = await this.orderStore.createReview(
                this.authStore.token,
                this.selectedOrder.id,
                this.reviewForm.rating,
                this.reviewForm.comment.trim()
            );

            if (result.success) {
                this.closeReviewSlide();
            }
        },
        getStarClass(star) {
            return star <= this.reviewForm.rating
                ? "text-yellow-400"
                : "text-gray-300 dark:text-gray-600";
        },
        animateStarHover(star) {
            const starElement = event.target;
            gsap.to(starElement, {
                scale: 1.2,
                duration: 0.2,
                ease: "power2.out",
            });
        },
        animateStarLeave(star) {
            const starElement = event.target;
            gsap.to(starElement, {
                scale: 1,
                duration: 0.2,
                ease: "power2.out",
            });
        },
        animateButtonHover(event) {
            gsap.to(event.target, {
                scale: 1.05,
                duration: 0.2,
                ease: "power2.out",
            });
        },
        animateButtonLeave(event) {
            gsap.to(event.target, {
                scale: 1,
                duration: 0.2,
                ease: "power2.out",
            });
        },
    },
};
</script>

<template>
    <div
        class="lg:col-span-2 bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 sm:p-10 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
    >
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Заказы
                </h2>
                <p
                    v-if="orderStore.pagination.total > 0"
                    class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                >
                    Показано {{ orderStore.orders.length }} из
                    {{ orderStore.pagination.total }} заказов
                </p>
            </div>
        </div>

        <!-- Загрузка заказов -->
        <div
            v-if="orderStore.isLoading"
            class="flex items-center justify-center py-12"
        >
            <div class="text-center">
                <div
                    class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"
                ></div>
                <p class="text-gray-600 dark:text-gray-400">
                    Загрузка заказов...
                </p>
            </div>
        </div>

        <!-- Ошибка загрузки -->
        <div v-else-if="orderStore.error" class="text-center py-12">
            <div
                class="bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-6 py-4 rounded-2xl dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400"
            >
                <p>{{ orderStore.error }}</p>
            </div>
        </div>

        <!-- Список заказов -->
        <div
            v-else-if="orderStore.orders && orderStore.orders.length > 0"
            class="divide-y divide-white/30 dark:divide-gray-700/30"
        >
            <div
                v-for="order in orderStore.orders"
                :key="order.id"
                class="py-5 sm:py-6 relative"
            >
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                class="text-lg font-bold text-gray-900 dark:text-gray-100"
                            >
                                Заказ #{{ order.order_number || order.id }}
                            </div>
                            <span
                                :class="[
                                    'px-3 py-1 rounded-lg text-xs font-medium border shadow-sm',
                                    getStatusClass(order.status),
                                ]"
                            >
                                {{ getStatusText(order.status) }}
                            </span>
                        </div>

                        <div
                            class="text-gray-500 dark:text-gray-400 text-sm mb-2"
                        >
                            {{ formatDate(order.created_at) }} ·
                            {{ getTypeText(order.type) }}
                        </div>

                        <div
                            v-if="order.problem_description"
                            class="text-gray-600 dark:text-gray-300 text-sm mb-2 line-clamp-2"
                        >
                            {{ order.problem_description }}
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 text-sm">
                                <div
                                    v-if="order.estimated_price"
                                    class="text-blue-600 dark:text-blue-400 font-medium"
                                >
                                    Ориентировочно:
                                    {{ formatPrice(order.estimated_price) }}
                                </div>
                                <div
                                    v-if="order.actual_price"
                                    class="text-green-600 dark:text-green-400 font-medium"
                                >
                                    К оплате:
                                    {{ formatPrice(order.actual_price) }}
                                </div>
                                <div
                                    v-if="order.urgency === 'urgent'"
                                    class="text-red-600 dark:text-red-400 font-medium"
                                >
                                    Срочный
                                </div>
                            </div>

                            <!-- Кнопка "Оставить отзыв" для завершенных заказов -->
                            <button
                                v-if="canLeaveReview(order)"
                                @click="openReviewSlide(order)"
                                class="bg-green-500/20 hover:bg-green-500/30 text-green-700 dark:text-green-400 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 border border-green-500/30 dark:border-green-500/30"
                            >
                                ⭐ Оставить отзыв
                            </button>
                        </div>

                        <!-- Раскрывающийся блок для создания отзыва -->
                        <div
                            v-if="
                                showReviewSlide &&
                                selectedOrder &&
                                selectedOrder.id === order.id
                            "
                            class="review-slide mt-4 bg-white/85 backdrop-blur-2xl rounded-2xl shadow-xl border border-white/25 dark:bg-gray-800/85 dark:border-gray-700/25 overflow-hidden"
                        >
                            <!-- Заголовок -->
                            <div
                                class="review-header flex items-center justify-between p-4 border-b border-gray-200/50 dark:border-gray-700/50"
                            >
                                <h4
                                    class="text-lg font-bold text-gray-900 dark:text-gray-100"
                                >
                                    Оставить отзыв
                                </h4>
                                <button
                                    @click="closeReviewSlide"
                                    class="p-1 hover:bg-gray-100/50 rounded-lg transition-colors duration-200 dark:hover:bg-gray-800/50"
                                >
                                    <svg
                                        class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        ></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Контент -->
                            <div class="review-content p-4 space-y-4">
                                <!-- Информация о заказе -->
                                <div
                                    class="bg-gray-50/80 backdrop-blur-lg rounded-xl p-3 border border-gray-200/30 dark:bg-gray-800/30 dark:border-gray-700/30"
                                >
                                    <h5
                                        class="font-medium text-gray-900 dark:text-gray-100 text-sm"
                                    >
                                        Заказ #{{
                                            order.order_number || order.id
                                        }}
                                    </h5>
                                    <p
                                        class="text-xs text-gray-600 dark:text-gray-400"
                                    >
                                        {{ getTypeText(order.type) }} ·
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>

                                <!-- Рейтинг -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Оценка
                                    </label>
                                    <div class="flex items-center gap-1">
                                        <button
                                            v-for="star in 5"
                                            :key="star"
                                            @click="reviewForm.rating = star"
                                            @mouseenter="animateStarHover(star)"
                                            @mouseleave="animateStarLeave(star)"
                                            :class="[
                                                'text-xl transition-colors duration-200',
                                                getStarClass(star),
                                            ]"
                                        >
                                            ⭐
                                        </button>
                                        <span
                                            class="ml-2 text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            {{ reviewForm.rating }} из 5
                                        </span>
                                    </div>
                                </div>

                                <!-- Комментарий -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Комментарий
                                    </label>
                                    <textarea
                                        v-model="reviewForm.comment"
                                        placeholder="Расскажите о качестве выполненной работы..."
                                        class="w-full px-3 py-2 bg-white/60 backdrop-blur-md border border-gray-200/50 rounded-xl shadow-lg focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500/50 transition-all duration-300 dark:bg-gray-800/60 dark:border-gray-600/50 dark:text-gray-100 dark:placeholder-gray-400"
                                        rows="3"
                                        maxlength="1000"
                                    ></textarea>
                                    <div
                                        class="text-right text-xs text-gray-500 dark:text-gray-400 mt-1"
                                    >
                                        {{ reviewForm.comment.length }}/1000
                                    </div>
                                </div>
                            </div>

                            <!-- Кнопки -->
                            <div
                                class="review-buttons p-4 border-t border-gray-200/50 dark:border-gray-700/50"
                            >
                                <div class="flex gap-2">
                                    <button
                                        @click="closeReviewSlide"
                                        @mouseenter="animateButtonHover"
                                        @mouseleave="animateButtonLeave"
                                        class="flex-1 px-3 py-2 bg-gray-100/60 hover:bg-gray-200/60 text-gray-700 rounded-lg text-sm font-medium transition-all duration-300 dark:bg-gray-800/60 dark:hover:bg-gray-700/60 dark:text-gray-300"
                                    >
                                        Отмена
                                    </button>
                                    <button
                                        @click="submitReview"
                                        @mouseenter="animateButtonHover"
                                        @mouseleave="animateButtonLeave"
                                        :disabled="
                                            !reviewForm.comment.trim() ||
                                            orderStore.createReviewLoading
                                        "
                                        :class="[
                                            'flex-1 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300',
                                            !reviewForm.comment.trim() ||
                                            orderStore.createReviewLoading
                                                ? 'bg-gray-100/60 text-gray-400 cursor-not-allowed dark:bg-gray-800/60 dark:text-gray-500'
                                                : 'bg-green-500/90 hover:bg-green-600/90 text-white shadow-lg hover:shadow-xl',
                                        ]"
                                    >
                                        <span
                                            v-if="
                                                orderStore.createReviewLoading
                                            "
                                        >
                                            Отправка...
                                        </span>
                                        <span v-else>Отправить</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Пустой список -->
        <div v-else class="text-center py-12">
            <div
                class="bg-gray-50/80 backdrop-blur-lg border border-gray-300/50 text-gray-700 px-6 py-4 rounded-2xl dark:bg-gray-800/30 dark:border-gray-600/50 dark:text-gray-400"
            >
                <p>У вас пока нет заказов</p>
                <button
                    class="mt-3 bg-blue-600/90 backdrop-blur-xs hover:bg-blue-700/90 text-white px-6 py-2 rounded-xl font-medium transition-all duration-300 dark:bg-blue-500/90 dark:hover:bg-blue-600/90"
                >
                    Создать первый заказ
                </button>
            </div>
        </div>

        <!-- Пагинация -->
        <div
            v-if="
                orderStore.orders &&
                orderStore.orders.length > 0 &&
                orderStore.pagination.last_page > 1
            "
            class="mt-8 flex items-center justify-between"
        >
            <div class="flex items-center gap-2">
                <button
                    @click="loadPage(orderStore.pagination.current_page - 1)"
                    :disabled="orderStore.pagination.current_page <= 1"
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300',
                        orderStore.pagination.current_page <= 1
                            ? 'bg-gray-100/50 text-gray-400 cursor-not-allowed dark:bg-gray-700/50 dark:text-gray-500'
                            : 'bg-white/60 hover:bg-white/80 text-gray-700 hover:text-gray-900 border border-white/20 dark:bg-gray-800/60 dark:hover:bg-gray-700/80 dark:text-gray-300 dark:hover:text-gray-100 dark:border-gray-700/20',
                    ]"
                >
                    Назад
                </button>

                <div class="flex items-center gap-1">
                    <button
                        v-for="page in getVisiblePages()"
                        :key="page"
                        @click="loadPage(page)"
                        :class="[
                            'px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300',
                            page === orderStore.pagination.current_page
                                ? 'bg-blue-500/20 text-blue-700 border border-blue-600/30 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30'
                                : 'bg-white/60 hover:bg-white/80 text-gray-700 hover:text-gray-900 border border-white/20 dark:bg-gray-800/60 dark:hover:bg-gray-700/80 dark:text-gray-300 dark:hover:text-gray-100 dark:border-gray-700/20',
                        ]"
                    >
                        {{ page }}
                    </button>
                </div>

                <button
                    @click="loadPage(orderStore.pagination.current_page + 1)"
                    :disabled="!orderStore.pagination.has_more_pages"
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300',
                        !orderStore.pagination.has_more_pages
                            ? 'bg-gray-100/50 text-gray-400 cursor-not-allowed dark:bg-gray-700/50 dark:text-gray-500'
                            : 'bg-white/60 hover:bg-white/80 text-gray-700 hover:text-gray-900 border border-white/20 dark:bg-gray-800/60 dark:hover:bg-gray-700/80 dark:text-gray-300 dark:hover:text-gray-100 dark:border-gray-700/20',
                    ]"
                >
                    Вперед
                </button>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                Страница {{ orderStore.pagination.current_page }} из
                {{ orderStore.pagination.last_page }}
            </div>
        </div>
    </div>
</template>

<style scoped></style>
