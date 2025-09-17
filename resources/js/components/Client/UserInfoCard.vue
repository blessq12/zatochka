<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "UserInfoCard",
    computed: {
        ...mapStores(useAuthStore),
    },
};
</script>

<template>
    <div
        class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 sm:p-10 lg:p-12 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
    >
        <div class="flex items-center justify-between mb-10 sm:mb-12 lg:mb-14">
            <div>
                <h1
                    class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100"
                >
                    Добро пожаловать,
                    {{ authStore.user?.full_name || "Пользователь" }}
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
                <button
                    @click="authStore.logout()"
                    class="bg-pink-600/90 backdrop-blur-xs hover:bg-pink-700/90 text-white px-6 py-3 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-pink-500/90 dark:hover:bg-pink-600/90"
                >
                    Выйти
                </button>
            </div>
        </div>

        <!-- Бонусный аккаунт -->
        <div
            class="grid grid-cols-1 sm:grid-cols-3 gap-8 sm:gap-10 lg:gap-12 mb-12 sm:mb-14 lg:mb-16"
        >
            <div
                class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
            >
                <div class="text-center">
                    <div
                        class="text-3xl font-black text-dark-blue dark:text-blue-400 mb-2"
                    >
                        {{ authStore.bonusAccount?.balance || 0 }}
                    </div>
                    <div class="text-gray-700 dark:text-gray-300">
                        Бонусных баллов
                    </div>
                </div>
            </div>

            <div
                class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
            >
                <div class="text-center">
                    <div
                        class="text-3xl font-black text-pink-600 dark:text-pink-400 mb-2"
                    >
                        {{ authStore.user?.phone || "Не указан" }}
                    </div>
                    <div class="text-gray-700 dark:text-gray-300">Телефон</div>
                </div>
            </div>

            <div
                class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
            >
                <div class="text-center">
                    <div
                        class="text-3xl font-black text-blue-600 dark:text-blue-400 mb-2"
                    >
                        {{ authStore.user?.email || "Не указан" }}
                    </div>
                    <div class="text-gray-700 dark:text-gray-300">Email</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
