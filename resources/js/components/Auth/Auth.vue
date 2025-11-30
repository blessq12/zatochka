<script>
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "Auth",
    data() {
        return {
            currentForm: "login",
        };
    },
    computed: {
        authStore() {
            return useAuthStore();
        },
        isAuthenticated() {
            return this.authStore.isAuthenticated;
        },
    },
    methods: {
        handleSuccess() {
            this.currentForm = "login";
        },
    },
};
</script>

<template>
    <div
        class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-8 sm:py-12 lg:py-16"
    >
        <div
            class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 sm:p-10 lg:p-12 border border-white/25 dark:bg-dark-blue-500/85 dark:backdrop-blur-2xl dark:border-gray-800/25"
        >
            <div class="flex items-center justify-between mb-8">
                <h1
                    class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100"
                >
                    Аутентификация
                </h1>
            </div>

            <div class="px-2 sm:px-4 lg:px-6">
                <div class="flex items-center justify-center">
                    <div
                        class="inline-flex items-center gap-1 bg-white/60 backdrop-blur-lg border border-white/20 rounded-2xl p-1 shadow-lg dark:bg-gray-800/60 dark:border-gray-700/20"
                    >
                        <button
                            @click="currentForm = 'login'"
                            :class="[
                                'px-6 py-3 text-lg rounded-xl transition-all duration-300',
                                currentForm === 'login'
                                    ? 'bg-blue-600/90 backdrop-blur-xs text-white border border-blue-600/20 shadow-lg dark:bg-blue-500/90 dark:text-white dark:border-blue-500/20'
                                    : 'text-gray-700 hover:text-pink-600 dark:text-gray-300 dark:hover:text-pink-400',
                            ]"
                        >
                            Вход
                        </button>
                        <button
                            @click="currentForm = 'register'"
                            :class="[
                                'px-6 py-3 text-lg rounded-xl transition-all duration-300',
                                currentForm === 'register'
                                    ? 'bg-blue-600/90 backdrop-blur-xs text-white border border-blue-600/20 shadow-lg dark:bg-blue-500/90 dark:text-white dark:border-blue-500/20'
                                    : 'text-gray-700 hover:text-pink-600 dark:text-gray-300 dark:hover:text-pink-400',
                            ]"
                        >
                            Регистрация
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-2 sm:mt-10 lg:mt-12">
                <div v-if="isAuthenticated" class="text-center">
                    <div
                        class="bg-green-50/80 backdrop-blur-lg border border-green-300/50 text-green-700 px-6 py-4 rounded-2xl dark:bg-green-900/30 dark:border-green-600/50 dark:text-green-400 mb-6"
                    >
                        <h3 class="text-lg font-semibold mb-2">
                            Успешная авторизация!
                        </h3>
                        <p>Вы успешно вошли в систему.</p>
                    </div>
                </div>

                <div v-else>
                    <div v-if="currentForm === 'login'">
                        <LoginForm @success="handleSuccess" />
                    </div>
                    <div v-else>
                        <RegisterForm @success="handleSuccess" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
