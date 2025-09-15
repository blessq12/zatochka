<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";
import { useOrderStore } from "../stores/orderStore.js";

export default {
    name: "AuthorizedApp",
    data() {
        return {
            demoUser: { name: "Иван Петров" },
            demoStats: [
                { label: "Активные заказы", value: 3 },
                { label: "Завершено", value: 12 },
                { label: "Бонусы", value: 540 },
            ],
            demoOrders: [
                {
                    id: "ORD-1024",
                    title: "Заточка ножниц Jaguar",
                    status: "В работе",
                    date: "12.09.2025",
                },
                {
                    id: "ORD-1023",
                    title: "Заточка ножа Tojiro",
                    status: "Готово",
                    date: "10.09.2025",
                },
                {
                    id: "ORD-1019",
                    title: "Заточка бритвы Feather",
                    status: "На приемке",
                    date: "05.09.2025",
                },
            ],
        };
    },
    computed: {
        ...mapStores(useAuthStore, useOrderStore),
    },
};
</script>

<template>
    <div
        class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-8 sm:py-12 lg:py-16"
    >
        <div
            class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 sm:p-10 lg:p-12 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
        >
            <div
                class="flex items-center justify-between mb-10 sm:mb-12 lg:mb-14"
            >
                <div>
                    <h1
                        class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100"
                    >
                        Добро пожаловать, {{ demoUser.name }}
                    </h1>
                    <p class="text-gray-700 dark:text-gray-300 mt-2">
                        Ваш персональный кабинет клиента
                    </p>
                </div>
                <div class="hidden sm:flex items-center gap-3">
                    <button
                        class="bg-blue-600/90 backdrop-blur-xs hover:bg-blue-700/90 text-white px-6 py-3 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-blue-500/90 dark:hover:bg-blue-600/90"
                    >
                        Новый заказ
                    </button>
                    <button
                        class="bg-white/60 backdrop-blur-xs hover:bg-white/80 text-gray-900 px-6 py-3 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-white/20 dark:bg-gray-800/60 dark:hover:bg-gray-700/80 dark:text-gray-100 dark:border-gray-700/20"
                    >
                        История
                    </button>
                </div>
            </div>

            <div
                class="grid grid-cols-1 sm:grid-cols-3 gap-8 sm:gap-10 lg:gap-12 mb-12 sm:mb-14 lg:mb-16"
            >
                <div
                    v-for="(stat, i) in demoStats"
                    :key="i"
                    class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
                >
                    <div
                        class="text-3xl font-black text-dark-blue dark:text-blue"
                    >
                        {{ stat.value }}
                    </div>
                    <div class="text-gray-700 dark:text-gray-300 mt-2">
                        {{ stat.label }}
                    </div>
                </div>
            </div>

            <div
                class="grid grid-cols-1 lg:grid-cols-3 gap-8 sm:gap-10 lg:gap-12"
            >
                <div
                    class="lg:col-span-2 bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 sm:p-10 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
                >
                    <div class="flex items-center justify-between mb-6 sm:mb-8">
                        <h2
                            class="text-xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            Недавние заказы
                        </h2>
                        <button
                            class="text-blue-600 dark:text-blue-400 hover:underline"
                        >
                            Все заказы
                        </button>
                    </div>
                    <div
                        class="divide-y divide-white/30 dark:divide-gray-700/30"
                    >
                        <div
                            v-for="order in demoOrders"
                            :key="order.id"
                            class="py-5 sm:py-6 flex items-center justify-between"
                        >
                            <div>
                                <div
                                    class="text-gray-900 dark:text-gray-100 font-medium"
                                >
                                    {{ order.title }}
                                </div>
                                <div
                                    class="text-gray-500 dark:text-gray-400 text-sm mt-2"
                                >
                                    {{ order.id }} · {{ order.date }}
                                </div>
                            </div>
                            <span
                                :class="[
                                    'px-4 py-2 rounded-xl text-sm font-medium border shadow-sm',
                                    order.status === 'Готово'
                                        ? 'bg-green-500/20 text-green-700 border-green-600/30 dark:bg-green-500/20 dark:text-green-300 dark:border-green-500/30'
                                        : order.status === 'В работе'
                                        ? 'bg-yellow-500/20 text-yellow-700 border-yellow-600/30 dark:bg-yellow-500/20 dark:text-yellow-300 dark:border-yellow-500/30'
                                        : 'bg-blue-500/20 text-blue-700 border-blue-600/30 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30',
                                ]"
                            >
                                {{ order.status }}
                            </span>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 sm:p-10 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
                >
                    <h2
                        class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6"
                    >
                        Быстрые действия
                    </h2>
                    <div class="flex flex-col gap-4">
                        <button
                            class="bg-blue-600/90 backdrop-blur-xs hover:bg-blue-700/90 text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:bg-blue-500/90 dark:hover:bg-blue-600/90"
                        >
                            Оформить новый заказ
                        </button>
                        <button
                            class="bg-white/60 backdrop-blur-xs hover:bg-white/80 text-gray-900 px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-white/20 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:bg-gray-800/60 dark:hover:bg-gray-700/80 dark:text-gray-100 dark:border-gray-700/20"
                        >
                            Профиль и настройки
                        </button>
                        <button
                            class="bg-pink-600/90 backdrop-blur-xs hover:bg-pink-700/90 text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-pink-500/40 dark:bg-pink-500/90 dark:hover:bg-pink-600/90"
                        >
                            Поддержка
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
