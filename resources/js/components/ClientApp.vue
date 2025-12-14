<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";
import { useOrderStore } from "../stores/orderStore.js";
import AuthorizedApp from "./AuthorizedApp.vue";
import LoginForm from "./ClientApp/LoginForm.vue";
import RegisterForm from "./ClientApp/RegisterForm.vue";
import PageHero from "./Layout/PageHero.vue";

export default {
    name: "ClientApp",
    components: {
        AuthorizedApp,
        LoginForm,
        RegisterForm,
        PageHero,
    },
    data() {
        return {
            activeForm: "login",
        };
    },
    computed: {
        ...mapStores(useAuthStore, useOrderStore),
    },
    async mounted() {
        await this.authStore.checkAuth();
    },
};
</script>

<template>
    <div v-if="authStore.isAuthenticated">
        <AuthorizedApp />
    </div>
    <div v-else class="min-h-screen bg-white dark:bg-dark-blue-500">
        <!-- Секция авторизации -->
        <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
            <div class="max-w-2xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
                <!-- Табы -->
                <div
                    class="flex gap-4 mb-8 sm:mb-12 bg-white/60 backdrop-blur-md p-2 border border-white/20 dark:bg-gray-800/60 dark:border-gray-700/20"
                >
                    <button
                        @click="activeForm = 'login'"
                        :class="[
                            'flex-1 px-6 py-4 font-jost-bold text-lg transition-all duration-300',
                            activeForm === 'login'
                                ? 'bg-[#C3006B] text-white shadow-lg'
                                : 'text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80',
                        ]"
                    >
                        Вход
                    </button>
                    <button
                        @click="activeForm = 'register'"
                        :class="[
                            'flex-1 px-6 py-4 font-jost-bold text-lg transition-all duration-300',
                            activeForm === 'register'
                                ? 'bg-[#C3006B] text-white shadow-lg'
                                : 'text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80',
                        ]"
                    >
                        Регистрация
                    </button>
                </div>

                <!-- Формы -->
                <LoginForm v-if="activeForm === 'login'" />
                <RegisterForm v-if="activeForm === 'register'" />
            </div>
        </section>
    </div>
</template>

<style scoped></style>
