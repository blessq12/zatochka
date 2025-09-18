<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "TelegramVerify",
    computed: {
        ...mapStores(useAuthStore),
        isTelegramConnected() {
            return (
                this.authStore.user?.telegram &&
                this.authStore.user.telegram.length > 0
            );
        },
    },
    methods: {
        async connectTelegram() {
            console.log("Подключение Telegram...");
        },
    },
};
</script>

<template>
    <div
        class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 sm:p-10 lg:p-12 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25 mt-12"
    >
        <!-- Заголовок секции -->
        <div class="flex items-center mb-8">
            <div
                class="w-12 h-12 bg-blue-600/90 backdrop-blur-xs rounded-2xl flex items-center justify-center mr-4 dark:bg-blue-500/90"
            >
                <svg
                    class="w-6 h-6 text-white"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.568 8.16l-1.61 7.59c-.12.54-.44.68-.89.42l-2.46-1.81-1.19 1.14c-.13.13-.24.24-.49.24l.18-2.55 4.57-4.13c.2-.18-.04-.28-.31-.1l-5.64 3.55-2.43-.76c-.53-.17-.54-.53.11-.78l9.57-3.69c.44-.16.83.1.69.78z"
                    />
                </svg>
            </div>
            <div>
                <h2
                    class="text-2xl font-jost-bold text-gray-900 dark:text-gray-100"
                >
                    Telegram
                </h2>
                <p class="text-gray-700 dark:text-gray-300">
                    Уведомления и поддержка
                </p>
            </div>
        </div>

        <!-- Загрузка данных пользователя -->
        <div
            v-if="authStore.isLoading"
            class="flex items-center justify-center py-12"
        >
            <div class="text-center">
                <div
                    class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"
                ></div>
                <p class="text-gray-700 dark:text-gray-300">
                    Загрузка данных...
                </p>
            </div>
        </div>

        <!-- Ошибка загрузки -->
        <div v-else-if="authStore.error" class="text-center py-12">
            <div
                class="bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-6 py-4 rounded-2xl dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400"
            >
                <p>{{ authStore.error }}</p>
                <button
                    @click="authStore.clearError()"
                    class="mt-3 text-red-600 hover:text-red-800 underline"
                >
                    Попробовать снова
                </button>
            </div>
        </div>

        <!-- Статус подключения -->
        <div
            v-else-if="isTelegramConnected"
            class="flex items-center justify-between"
        >
            <div class="flex items-center">
                <div
                    class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-4"
                >
                    <svg
                        class="w-5 h-5 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        ></path>
                    </svg>
                </div>
                <div>
                    <p
                        class="text-lg font-jost-medium text-gray-900 dark:text-gray-100"
                    >
                        Telegram подключен
                    </p>
                    <p class="text-gray-700 dark:text-gray-300">
                        @{{ authStore.user?.telegram }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Призыв к подключению -->
        <div v-else-if="authStore.user && !isTelegramConnected">
            <div
                class="bg-blue-50/80 backdrop-blur-lg border border-blue-200/30 rounded-2xl p-6 mb-6 dark:bg-blue-900/30 dark:backdrop-blur-lg dark:border-blue-800/20"
            >
                <div class="flex items-start">
                    <div
                        class="w-8 h-8 bg-blue-600/90 rounded-xl flex items-center justify-center mr-4 flex-shrink-0 dark:bg-blue-500/90"
                    >
                        <svg
                            class="w-5 h-5 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                    </div>
                    <div>
                        <h3
                            class="text-lg font-jost-bold text-blue-900 dark:text-gray-100 mb-2"
                        >
                            Подключите Telegram
                        </h3>
                        <p class="text-blue-800 dark:text-gray-300 mb-4">
                            Получайте уведомления о статусе заказов, важных
                            обновлениях и получайте быструю поддержку через
                            нашего чат-бота в Telegram.
                        </p>
                        <ul class="text-blue-800 dark:text-gray-300 space-y-2">
                            <li class="flex items-center">
                                <svg
                                    class="w-4 h-4 mr-2 flex-shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                                Уведомления о статусе заказов
                            </li>
                            <li class="flex items-center">
                                <svg
                                    class="w-4 h-4 mr-2 flex-shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                                Быстрая поддержка 24/7
                            </li>
                            <li class="flex items-center">
                                <svg
                                    class="w-4 h-4 mr-2 flex-shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                                Эксклюзивные предложения
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <button
                @click="connectTelegram"
                class="w-full bg-blue-600/90 backdrop-blur-xs hover:bg-blue-700/90 text-white px-8 py-4 rounded-2xl font-jost-bold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center dark:bg-blue-500/90 dark:hover:bg-blue-600/90"
            >
                <svg
                    class="w-5 h-5 mr-3"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.568 8.16l-1.61 7.59c-.12.54-.44.68-.89.42l-2.46-1.81-1.19 1.14c-.13.13-.24.24-.49.24l.18-2.55 4.57-4.13c.2-.18-.04-.28-.31-.1l-5.64 3.55-2.43-.76c-.53-.17-.54-.53.11-.78l9.57-3.69c.44-.16.83.1.69.78z"
                    />
                </svg>
                Подключить Telegram
            </button>
        </div>
    </div>
</template>

<style scoped></style>
