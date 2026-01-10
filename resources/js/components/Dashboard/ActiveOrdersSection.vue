<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";
import { useOrderStore } from "../../stores/orderStore.js";

export default {
    name: "ActiveOrdersSection",
    computed: {
        ...mapStores(useAuthStore, useOrderStore),
        activeOrders() {
            if (!this.orderStore.orders || this.orderStore.orders.length === 0) {
                return [];
            }
            return this.orderStore.orders.filter(
                (order) =>
                    order.status !== "issued" && order.status !== "cancelled"
            );
        },
        isLoading() {
            return this.orderStore.isLoading;
        },
    },
    methods: {
        getStatusLabel(status) {
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
            return statusMap[status] || status;
        },
        getStatusColor(status) {
            const colorMap = {
                new: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300",
                consultation: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300",
                diagnostic: "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300",
                in_work: "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300",
                waiting_parts: "bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300",
                ready: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300",
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
                АКТИВНЫЕ ЗАКАЗЫ
            </h2>

            <div v-if="isLoading" class="mt-4 text-center py-12">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#C20A6C] mx-auto mb-4"
                ></div>
                <p class="text-gray-600 dark:text-gray-400">
                    Загрузка заказов...
                </p>
            </div>

            <div v-else-if="activeOrders.length === 0" class="mt-4 text-center py-12">
                <p
                    class="text-dark-gray-500 dark:text-gray-200 font-jost-regular text-base sm:text-lg"
                >
                    У вас пока нет активных заказов
                </p>
            </div>

            <div v-else class="mt-4 space-y-4">
                <div
                    v-for="order in activeOrders"
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
                                        Создан:
                                    </span>
                                    <span
                                        class="ml-2 font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                    >
                                        {{ formatDate(order.created_at) }}
                                    </span>
                                </div>
                                <div v-if="order.estimated_price">
                                    <span
                                        class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                    >
                                        Предварительная стоимость:
                                    </span>
                                    <span
                                        class="ml-2 font-jost-bold text-[#C3006B]"
                                    >
                                        {{ formatPrice(order.estimated_price) }}
                                    </span>
                                </div>
                                <div v-if="order.actual_price">
                                    <span
                                        class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                    >
                                        Фактическая стоимость:
                                    </span>
                                    <span
                                        class="ml-2 font-jost-bold text-[#C3006B]"
                                    >
                                        {{ formatPrice(order.actual_price) }}
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
            </div>
        </div>
    </div>
</template>

<style scoped></style>
