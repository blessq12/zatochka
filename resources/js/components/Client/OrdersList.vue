<script>
import { mapStores } from "pinia";
import { useOrderStore } from "../../stores/orderStore.js";

export default {
    name: "OrdersList",
    computed: {
        ...mapStores(useOrderStore),
    },
    methods: {
        getStatusClass(status) {
            switch (status) {
                case "completed":
                case "Готово":
                    return "bg-green-500/20 text-green-700 border-green-600/30 dark:bg-green-500/20 dark:text-green-300 dark:border-green-500/30";
                case "in_progress":
                case "В работе":
                    return "bg-yellow-500/20 text-yellow-700 border-yellow-600/30 dark:bg-yellow-500/20 dark:text-yellow-300 dark:border-yellow-500/30";
                case "pending":
                case "Ожидает":
                    return "bg-blue-500/20 text-blue-700 border-blue-600/30 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30";
                default:
                    return "bg-gray-500/20 text-gray-700 border-gray-600/30 dark:bg-gray-500/20 dark:text-gray-300 dark:border-gray-500/30";
            }
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
    },
};
</script>

<template>
    <div
        class="lg:col-span-2 bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 sm:p-10 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
    >
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                Недавние заказы
            </h2>
            <button class="text-blue-600 dark:text-blue-400 hover:underline">
                Все заказы
            </button>
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
                v-for="order in orderStore.orders.slice(0, 5)"
                :key="order.id"
                class="py-5 sm:py-6 flex items-center justify-between"
            >
                <div>
                    <div class="text-gray-900 dark:text-gray-100 font-medium">
                        Заказ #{{ order.id }}
                    </div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm mt-2">
                        {{ formatDate(order.created_at) }} ·
                        {{ order.items_count || 0 }} позиций
                    </div>
                    <div
                        v-if="order.description"
                        class="text-gray-600 dark:text-gray-300 text-sm mt-1"
                    >
                        {{ order.description }}
                    </div>
                </div>
                <span
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-medium border shadow-sm',
                        getStatusClass(order.status),
                    ]"
                >
                    {{ order.status || "Неизвестно" }}
                </span>
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
    </div>
</template>

<style scoped></style>
