<script>
import { mapStores } from "pinia";
import { useOrderStore } from "../../stores/orderStore.js";
import {
    formatBillingType,
    formatOrderStatus,
    formatReviewStatus,
    formatServiceTypes,
    formatStars,
    formatUrgency,
} from "../../utils/serviceTypes.js";

export default {
    name: "OrdersHistorySection",
    data() {
        return {
            reviewModalOrder: null,
            reviewForm: {
                rating: 5,
                comment: "",
            },
            reviewErrors: {},
            reviewSubmitting: false,
        };
    },
    computed: {
        ...mapStores(useOrderStore),
        historyOrders() {
            return this.orderStore.historyOrders || [];
        },
        isLoading() {
            return this.orderStore.isLoadingHistory;
        },
        pagination() {
            return this.orderStore.historyPagination;
        },
        totalPages() {
            const { total, per_page } = this.pagination;
            if (!per_page) return 1;
            return Math.max(1, Math.ceil(total / per_page));
        },
    },
    methods: {
        formatServiceTypes,
        formatOrderStatus,
        formatBillingType,
        formatUrgency,
        formatReviewStatus,
        formatStars,
        async changePage(page) {
            if (page < 1 || page > this.totalPages) {
                return;
            }

            await this.orderStore.fetchHistoryOrders(
                page,
                this.pagination.per_page
            );
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
        formatDate(dateString) {
            if (!dateString) return "";
            const date = new Date(dateString);
            return date.toLocaleDateString("ru-RU", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
            });
        },
        formatPrice(price) {
            if (price === null || price === undefined || price === "") {
                return "—";
            }
            return new Intl.NumberFormat("ru-RU", {
                style: "currency",
                currency: "RUB",
            }).format(price);
        },
        commentText(order) {
            return order.client_comment || order.description || null;
        },
        canLeaveReview(order) {
            return !order.review_exists;
        },
        openReviewModal(order) {
            this.reviewModalOrder = order;
            this.reviewForm = { rating: 5, comment: "" };
            this.reviewErrors = {};
        },
        closeReviewModal() {
            this.reviewModalOrder = null;
            this.reviewErrors = {};
        },
        async submitReview() {
            this.reviewErrors = {};
            if (!this.reviewForm.comment.trim()) {
                this.reviewErrors.comment = "Напишите текст отзыва";
                return;
            }
            if (this.reviewForm.rating < 1 || this.reviewForm.rating > 5) {
                this.reviewErrors.rating = "Выберите оценку от 1 до 5";
                return;
            }
            this.reviewSubmitting = true;
            const result = await this.orderStore.createReview(
                this.reviewModalOrder.id,
                this.reviewForm.rating,
                this.reviewForm.comment.trim()
            );
            this.reviewSubmitting = false;
            if (result.success) {
                this.closeReviewModal();
            } else {
                this.reviewErrors.general = result.error;
            }
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <div
            class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
        >
            <h2
                class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[90%] px-4 sm:px-6 bg-white dark:bg-dark-blue-500 text-lg sm:text-xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C] text-center whitespace-nowrap"
            >
                ИСТОРИЯ ЗАКАЗОВ
            </h2>

            <div v-if="isLoading" class="mt-4 text-center py-12">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#C20A6C] mx-auto mb-4"
                ></div>
                <p class="text-gray-600 dark:text-gray-400">
                    Загрузка заказов...
                </p>
            </div>

            <div
                v-else-if="historyOrders.length === 0"
                class="mt-4 text-center py-12"
            >
                <p
                    class="text-dark-gray-500 dark:text-gray-200 font-jost-regular text-base sm:text-lg"
                >
                    У вас пока нет завершённых заказов
                </p>
            </div>

            <div v-else class="mt-4 space-y-4">
                <div
                    v-for="order in historyOrders"
                    :key="order.id"
                    class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 py-6 bg-white/60 backdrop-blur-md dark:bg-gray-800/60 hover:shadow-lg transition-all duration-300"
                >
                    <div class="flex-1">
                        <div
                            class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3"
                        >
                            <h3
                                class="text-lg sm:text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                            >
                                Заказ №{{ order.order_number }}
                            </h3>
                            <span
                                class="inline-flex self-start px-3 py-1 text-sm font-jost-medium rounded-full bg-dark-blue-500/10 text-dark-blue-500 dark:bg-dark-blue-300/20 dark:text-dark-blue-300"
                            >
                                {{ formatOrderStatus(order.status) }}
                            </span>
                        </div>
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm sm:text-base"
                        >
                            <div>
                                <span
                                    class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                >
                                    Тип:
                                </span>
                                <span
                                    class="ml-2 font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                >
                                    {{ formatServiceTypes(order.service_types) }}
                                </span>
                            </div>
                            <div>
                                <span
                                    class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                >
                                    Вид:
                                </span>
                                <span
                                    class="ml-2 font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                >
                                    {{ formatBillingType(order.billing_type) }}
                                </span>
                            </div>
                            <div>
                                <span
                                    class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                >
                                    Срочность:
                                </span>
                                <span
                                    class="ml-2 font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                >
                                    {{ formatUrgency(order.urgency) }}
                                </span>
                            </div>
                            <div>
                                <span
                                    class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                >
                                    Доставка:
                                </span>
                                <span
                                    class="ml-2 font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                >
                                    {{
                                        order.delivery_required
                                            ? "Нужна"
                                            : "Не требуется"
                                    }}
                                </span>
                            </div>
                            <div>
                                <span
                                    class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                >
                                    Дата:
                                </span>
                                <span
                                    class="ml-2 font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                >
                                    {{ formatDate(order.created_at) }}
                                </span>
                            </div>
                            <div>
                                <span
                                    class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                >
                                    Стоимость:
                                </span>
                                <span
                                    class="ml-2 font-jost-bold text-[#C3006B]"
                                >
                                    {{ formatPrice(order.price) }}
                                </span>
                            </div>
                        </div>
                        <div
                            v-if="commentText(order)"
                            class="mt-3 pt-3 border-t border-dark-blue-500/20 dark:border-dark-gray-200/20"
                        >
                            <p
                                class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300"
                            >
                                <span
                                    class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                >
                                    Комментарий:
                                </span>
                                {{ commentText(order) }}
                            </p>
                        </div>

                        <div
                            v-if="order.items && order.items.length"
                            class="mt-3 pt-3 border-t border-dark-blue-500/20 dark:border-dark-gray-200/20"
                        >
                            <p
                                class="text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                            >
                                Позиции заказа
                            </p>
                            <ul class="space-y-2">
                                <li
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300 flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1"
                                >
                                    <span>
                                        {{ item.title
                                        }}<template
                                            v-if="
                                                item.tool_type_label &&
                                                item.tool_type_label !==
                                                    item.title
                                            "
                                        >
                                            · {{ item.tool_type_label }}
                                        </template>
                                        <template v-if="item.quantity != null">
                                            · {{ item.quantity }} шт.
                                        </template>
                                    </span>
                                    <span
                                        class="text-dark-gray-400 dark:text-gray-400 shrink-0"
                                    >
                                        {{ item.status_label }}
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <div
                            v-if="order.review"
                            class="mt-3 pt-3 border-t border-dark-blue-500/20 dark:border-dark-gray-200/20 space-y-2"
                        >
                            <div
                                class="flex flex-wrap items-center gap-x-3 gap-y-1"
                            >
                                <span
                                    class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                >
                                    Ваш отзыв
                                </span>
                                <span
                                    class="text-[#C3006B] tracking-wide"
                                    :title="`${order.review.rating} из 5`"
                                >
                                    {{ formatStars(order.review.rating) }}
                                </span>
                                <span
                                    class="text-sm font-jost-regular text-dark-gray-400 dark:text-gray-400"
                                >
                                    {{
                                        formatReviewStatus(
                                            order.review.status ||
                                                order.review_status
                                        )
                                    }}
                                </span>
                            </div>
                            <p
                                v-if="order.review.comment"
                                class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300"
                            >
                                {{ order.review.comment }}
                            </p>
                            <div
                                v-if="order.review.manager_reply"
                                class="rounded-lg bg-dark-blue-500/5 dark:bg-white/5 px-4 py-3"
                            >
                                <p
                                    class="text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-1"
                                >
                                    Ответ сервиса
                                </p>
                                <p
                                    class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                >
                                    {{ order.review.manager_reply }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-else-if="order.review_exists"
                            class="mt-3 pt-3 border-t border-dark-blue-500/20 dark:border-dark-gray-200/20"
                        >
                            <p
                                class="text-sm font-jost-regular text-dark-gray-500 dark:text-gray-300"
                            >
                                Отзыв отправлен
                                <span
                                    v-if="order.review_status"
                                    class="text-gray-500 dark:text-gray-400"
                                >
                                    ({{
                                        formatReviewStatus(order.review_status)
                                    }})
                                </span>
                            </p>
                        </div>

                        <div
                            v-if="canLeaveReview(order)"
                            class="mt-3 pt-3 border-t border-dark-blue-500/20 dark:border-dark-gray-200/20"
                        >
                            <button
                                type="button"
                                @click="openReviewModal(order)"
                                class="text-sm sm:text-base font-jost-medium text-[#C3006B] hover:text-[#A8005A] dark:text-[#C20A6C] dark:hover:text-[#E01A7C] transition-colors"
                            >
                                Написать отзыв
                            </button>
                        </div>
                    </div>
                </div>

                <Teleport to="body">
                    <div
                        v-if="reviewModalOrder"
                        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div
                            class="bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-600 rounded-xl max-w-md w-full p-6 sm:p-8"
                            @click.stop
                        >
                            <h3
                                class="text-lg font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-1"
                            >
                                Отзыв на заказ №{{
                                    reviewModalOrder.order_number
                                }}
                            </h3>
                            <p
                                class="text-sm text-gray-600 dark:text-gray-400 mb-6"
                            >
                                Оцените качество услуги и оставьте комментарий.
                            </p>
                            <form
                                @submit.prevent="submitReview"
                                class="space-y-4"
                            >
                                <div
                                    v-if="reviewErrors.general"
                                    class="text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ reviewErrors.general }}
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Оценка
                                    </label>
                                    <select
                                        v-model.number="reviewForm.rating"
                                        class="w-full px-4 py-3 border rounded-lg bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-[#C3006B] focus:border-transparent"
                                        :class="{
                                            'border-red-500':
                                                reviewErrors.rating,
                                        }"
                                    >
                                        <option
                                            v-for="n in 5"
                                            :key="n"
                                            :value="n"
                                        >
                                            {{ n }}
                                            {{
                                                n === 1
                                                    ? "звезда"
                                                    : n < 5
                                                      ? "звезды"
                                                      : "звёзд"
                                            }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="reviewErrors.rating"
                                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ reviewErrors.rating }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Комментарий
                                    </label>
                                    <textarea
                                        v-model="reviewForm.comment"
                                        rows="4"
                                        placeholder="Расскажите о качестве услуги..."
                                        class="w-full px-4 py-3 border rounded-lg bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-[#C3006B] focus:border-transparent resize-none"
                                        :class="{
                                            'border-red-500':
                                                reviewErrors.comment,
                                        }"
                                    ></textarea>
                                    <p
                                        v-if="reviewErrors.comment"
                                        class="mt-1 text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ reviewErrors.comment }}
                                    </p>
                                </div>
                                <div class="flex gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="closeReviewModal"
                                        class="flex-1 px-4 py-3 font-jost-medium border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                                    >
                                        Отмена
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="reviewSubmitting"
                                        class="flex-1 px-4 py-3 font-jost-bold bg-[#C3006B] text-white rounded-lg hover:bg-[#A8005A] disabled:opacity-50 disabled:cursor-not-allowed transition"
                                    >
                                        {{
                                            reviewSubmitting
                                                ? "Отправка..."
                                                : "Отправить отзыв"
                                        }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </Teleport>

                <div
                    v-if="totalPages > 1"
                    class="flex items-center justify-center gap-2 pt-6 mt-6 border-t border-dark-blue-500/20 dark:border-dark-gray-200/20"
                >
                    <button
                        @click="changePage(pagination.page - 1)"
                        :disabled="pagination.page === 1"
                        class="px-4 py-2 font-jost-medium text-sm sm:text-base transition-all duration-300 border border-dark-blue-500/30 dark:border-dark-gray-200/90 text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Назад
                    </button>

                    <div class="flex gap-2">
                        <button
                            v-for="page in totalPages"
                            :key="page"
                            @click="changePage(page)"
                            :class="[
                                'px-4 py-2 font-jost-medium text-sm sm:text-base transition-all duration-300 border',
                                pagination.page === page
                                    ? 'bg-[#C3006B] text-white border-[#C3006B] shadow-lg'
                                    : 'border-dark-blue-500/30 dark:border-dark-gray-200/90 text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80',
                            ]"
                        >
                            {{ page }}
                        </button>
                    </div>

                    <button
                        @click="changePage(pagination.page + 1)"
                        :disabled="pagination.page === totalPages"
                        class="px-4 py-2 font-jost-medium text-sm sm:text-base transition-all duration-300 border border-dark-blue-500/30 dark:border-dark-gray-200/90 text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Вперёд
                    </button>
                </div>

                <div
                    v-if="historyOrders.length > 0"
                    class="text-center pt-4 text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300"
                >
                    Показано {{ historyOrders.length }} из
                    {{ pagination.total }} заказов
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
