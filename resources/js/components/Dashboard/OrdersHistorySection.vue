<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";
import { useOrderStore } from "../../stores/orderStore.js";

export default {
    name: "OrdersHistorySection",
    data() {
        return {
            currentPage: 1,
            perPage: 10,
        };
    },
    computed: {
        ...mapStores(useAuthStore, useOrderStore),
        historyOrders() {
            if (
                !this.orderStore.orders ||
                this.orderStore.orders.length === 0
            ) {
                return [];
            }
            return this.orderStore.orders.filter(
                (order) =>
                    order.status === "issued" || order.status === "cancelled"
            );
        },
        isLoading() {
            return this.orderStore.isLoading;
        },
        pagination() {
            return (
                this.orderStore.pagination || {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0,
                    has_more_pages: false,
                }
            );
        },
        paginatedOrders() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.historyOrders.slice(start, end);
        },
        totalPages() {
            return Math.ceil(this.historyOrders.length / this.perPage);
        },
    },
    async mounted() {
        // Заказы уже загружаются в AuthorizedApp, но если их нет - загружаем
        if (!this.orderStore.orders || this.orderStore.orders.length === 0) {
            await this.orderStore.getClientOrders(this.authStore.token, 1, 50);
        }
    },
    methods: {
        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                window.scrollTo({ top: 0, behavior: "smooth" });
            }
        },
        getStatusLabel(status) {
            const statusMap = {
                new: "Новый",
                in_work: "В работе",
                waiting_parts: "Ожидание запчастей",
                ready: "Готов",
                issued: "Выдан",
                cancelled: "Отменен",
            };
            return statusMap[status] || status;
        },
        getStatusColor(status) {
            const colorMap = {
                issued: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300",
                cancelled:
                    "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300",
            };
            return (
                colorMap[status] ||
                "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300"
            );
        },
        getTypeLabel(type) {
            const typeMap = {
                repair: "Ремонт",
                sharpening: "Заточка",
                diagnostic: "Диагностика",
                replacement: "Замена",
                maintenance: "Обслуживание",
                consultation: "Консультация",
                warranty: "Гарантийный",
            };
            return typeMap[type] || type;
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
            if (!price) return "—";
            return new Intl.NumberFormat("ru-RU", {
                style: "currency",
                currency: "RUB",
            }).format(price);
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
                    У вас пока нет завершенных заказов
                </p>
            </div>

            <div v-else class="mt-4 space-y-4">
                <!-- Список заказов -->
                <div
                    v-for="order in paginatedOrders"
                    :key="order.id"
                    class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 py-6 bg-white/60 backdrop-blur-md dark:bg-gray-800/60 hover:shadow-lg transition-all duration-300"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                    >
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <h3
                                    class="text-lg sm:text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                                >
                                    Заказ №{{ order.order_number }}
                                </h3>
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs sm:text-sm font-jost-medium',
                                        getStatusColor(order.status),
                                    ]"
                                >
                                    {{ getStatusLabel(order.status) }}
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
                                        {{ getTypeLabel(order.type) }}
                                    </span>
                                </div>
                                <div>
                                    <span
                                        class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                    >
                                        Завершен:
                                    </span>
                                    <span
                                        class="ml-2 font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                    >
                                        {{ formatDate(order.updated_at) }}
                                    </span>
                                </div>
                                <div v-if="order.price">
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
                                v-if="order.problem_description"
                                class="mt-3 pt-3 border-t border-dark-blue-500/20 dark:border-dark-gray-200/20"
                            >
                                <p
                                    class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                >
                                    <span
                                        class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                    >
                                        Описание проблемы:
                                    </span>
                                    {{ order.problem_description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Пагинация -->
                <div
                    v-if="totalPages > 1"
                    class="flex items-center justify-center gap-2 pt-6 mt-6 border-t border-dark-blue-500/20 dark:border-dark-gray-200/20"
                >
                    <button
                        @click="changePage(currentPage - 1)"
                        :disabled="currentPage === 1"
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
                                currentPage === page
                                    ? 'bg-[#C3006B] text-white border-[#C3006B] shadow-lg'
                                    : 'border-dark-blue-500/30 dark:border-dark-gray-200/90 text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80',
                            ]"
                        >
                            {{ page }}
                        </button>
                    </div>

                    <button
                        @click="changePage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="px-4 py-2 font-jost-medium text-sm sm:text-base transition-all duration-300 border border-dark-blue-500/30 dark:border-dark-gray-200/90 text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Вперед
                    </button>
                </div>

                <!-- Информация о пагинации -->
                <div
                    v-if="historyOrders.length > 0"
                    class="text-center pt-4 text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300"
                >
                    Показано {{ paginatedOrders.length }} из
                    {{ historyOrders.length }} заказов
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
