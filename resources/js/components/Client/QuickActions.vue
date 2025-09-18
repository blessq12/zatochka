<script>
export default {
    name: "QuickActions",
    methods: {
        handleNewOrder() {
            // TODO: Реализовать логику создания нового заказа
            console.log("Создание нового заказа");
        },

        handleSupport() {
            // TODO: Реализовать переход к поддержке
            console.log("Переход к поддержке");
        },
    },
};
</script>

<template>
    <div>
        <div class="flex flex-row flex-wrap items-center gap-3 mb-6">
            <button
                @click="handleNewOrder"
                class="bg-blue-600/90 backdrop-blur-xs hover:bg-blue-700/90 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 shadow hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:bg-blue-500/90 dark:hover:bg-blue-600/90"
            >
                Оформить новый заказ
            </button>

            <button
                @click="handleSupport"
                class="bg-pink-600/90 backdrop-blur-xs hover:bg-pink-700/90 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 shadow hover:shadow-md focus:outline-none focus:ring-2 focus:ring-pink-500/40 dark:bg-pink-500/90 dark:hover:bg-pink-600/90"
            >
                Поддержка
            </button>
        </div>
    </div>
</template>

<style scoped></style>
