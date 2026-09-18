<script>
import { mapStores } from "pinia";
import { useOrderStore } from "../../stores/orderStore.js";
import {
    formatBillingType,
    formatOrderItems,
    formatOrderStatus,
    formatServiceTypes,
    formatUrgency,
    serviceTypesFromItems,
} from "../../utils/serviceTypes.js";

export default {
    name: "OrdersHistorySection",
    data() {
        return {
            reviewDrafts: {},
            reviewErrors: {},
            savingReviewId: null,
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
    },
    watch: {
        historyOrders: {
            immediate: true,
            handler(orders) {
                for (const order of orders) {
                    if (!this.reviewDrafts[order.id]) {
                        this.reviewDrafts[order.id] = { rating: 5, text: "" };
                    }
                }
            },
        },
    },
    methods: {
        formatServiceTypes,
        formatOrderStatus,
        formatBillingType,
        formatUrgency,
        formatOrderItems,
        serviceTypesFromItems,
        formatPrice(price) {
            if (price === null || price === undefined || price === "") {
                return "—";
            }
            return new Intl.NumberFormat("ru-RU", {
                style: "currency",
                currency: "RUB",
            }).format(price);
        },
        canReview(order) {
            return order.status === "issued" && !order.review;
        },
        async submitReview(order) {
            const draft = this.reviewDrafts[order.id] || { rating: 5, text: "" };
            this.savingReviewId = order.id;
            this.reviewErrors[order.id] = null;
            try {
                await this.orderStore.submitReview(order.id, {
                    rating: Number(draft.rating),
                    text: draft.text || null,
                });
            } catch (e) {
                this.reviewErrors[order.id] =
                    e.response?.data?.message || "Не удалось отправить отзыв";
            } finally {
                this.savingReviewId = null;
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
                    Загрузка истории...
                </p>
            </div>

            <div
                v-else-if="historyOrders.length === 0"
                class="mt-4 text-center py-12"
            >
                <p
                    class="text-dark-gray-500 dark:text-gray-200 font-jost-regular text-base sm:text-lg"
                >
                    Выданных заказов пока нет
                </p>
            </div>

            <div v-else class="mt-4 space-y-4">
                <div
                    v-for="order in historyOrders"
                    :key="order.id"
                    class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 py-6 bg-white/60 backdrop-blur-md dark:bg-gray-800/60"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3"
                    >
                        <h3
                            class="text-lg sm:text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                        >
                            Заказ №{{ order.id }}
                        </h3>
                        <span
                            class="inline-flex self-start px-3 py-1 text-sm font-jost-medium bg-[#C3006B]/10 text-[#C3006B]"
                        >
                            {{ formatOrderStatus(order.status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm sm:text-base">
                        <div>
                            <span class="font-jost-medium">Тип:</span>
                            <span class="ml-2">
                                {{
                                    formatServiceTypes(
                                        serviceTypesFromItems(order.items),
                                    )
                                }}
                            </span>
                        </div>
                        <div>
                            <span class="font-jost-medium">Вид:</span>
                            <span class="ml-2">
                                {{ formatBillingType(order.billing_type) }}
                            </span>
                        </div>
                        <div>
                            <span class="font-jost-medium">Стоимость:</span>
                            <span class="ml-2 font-jost-bold text-[#C3006B]">
                                {{ formatPrice(order.estimated_cost) }}
                            </span>
                        </div>
                        <div>
                            <span class="font-jost-medium">Состав:</span>
                            <span class="ml-2">
                                {{ formatOrderItems(order.items) }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="order.review"
                        class="mt-4 pt-4 border-t border-dark-blue-500/20"
                    >
                        <p class="font-jost-medium">
                            Отзыв: {{ order.review.rating }}/5
                        </p>
                        <p v-if="order.review.text" class="mt-1 text-sm">
                            {{ order.review.text }}
                        </p>
                    </div>

                    <div
                        v-else-if="canReview(order)"
                        class="mt-4 pt-4 border-t border-dark-blue-500/20 space-y-3"
                    >
                        <p class="font-jost-bold text-[#C3006B]">Оставить отзыв</p>
                        <p
                            v-if="reviewErrors[order.id]"
                            class="text-sm text-red-600"
                        >
                            {{ reviewErrors[order.id] }}
                        </p>
                        <label class="block text-sm">
                            Оценка
                            <select
                                v-model.number="reviewDrafts[order.id].rating"
                                class="mt-1 w-full border border-dark-blue-500/30 bg-white px-3 py-2 dark:bg-gray-800"
                            >
                                <option v-for="n in 5" :key="n" :value="n">
                                    {{ n }}
                                </option>
                            </select>
                        </label>
                        <label class="block text-sm">
                            Комментарий
                            <textarea
                                v-model="reviewDrafts[order.id].text"
                                rows="3"
                                class="mt-1 w-full border border-dark-blue-500/30 bg-white px-3 py-2 dark:bg-gray-800"
                            />
                        </label>
                        <button
                            type="button"
                            class="bg-[#C3006B] px-5 py-2 font-jost-bold text-white hover:bg-[#A8005A] disabled:opacity-50"
                            :disabled="savingReviewId === order.id"
                            @click="submitReview(order)"
                        >
                            Отправить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
