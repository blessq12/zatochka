<script>
export default {
    name: "QuickActions",
    methods: {
        handleNewOrder() {
            // TODO: Реализовать логику создания нового заказа
            console.log("Создание нового заказа");
        },
        handleProfileSettings() {
            // TODO: Реализовать переход к профилю и настройкам
            console.log("Переход к профилю и настройкам");
        },
        handleSupport() {
            // TODO: Реализовать переход к поддержке
            console.log("Переход к поддержке");
        },
    },
};
</script>

<template>
    <div
        class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 sm:p-10 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
    >
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">
            Быстрые действия
        </h2>
        <div class="flex flex-col gap-4">
            <button
                @click="handleNewOrder"
                class="bg-blue-600/90 backdrop-blur-xs hover:bg-blue-700/90 text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:bg-blue-500/90 dark:hover:bg-blue-600/90"
            >
                Оформить новый заказ
            </button>
            <button
                @click="handleProfileSettings"
                class="bg-white/60 backdrop-blur-xs hover:bg-white/80 text-gray-900 px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-white/20 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:bg-gray-800/60 dark:hover:bg-gray-700/80 dark:text-gray-100 dark:border-gray-700/20"
            >
                Профиль и настройки
            </button>
            <button
                @click="handleSupport"
                class="bg-pink-600/90 backdrop-blur-xs hover:bg-pink-700/90 text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-pink-500/40 dark:bg-pink-500/90 dark:hover:bg-pink-600/90"
            >
                Поддержка
            </button>
        </div>
    </div>
</template>

<style scoped></style>
