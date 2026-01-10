<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";
import { useOrderStore } from "../stores/orderStore.js";
import ActiveOrdersSection from "./Dashboard/ActiveOrdersSection.vue";
import OrdersHistorySection from "./Dashboard/OrdersHistorySection.vue";
import ProfileSection from "./Dashboard/ProfileSection.vue";
import TelegramSection from "./Dashboard/TelegramSection.vue";

export default {
    name: "AuthorizedApp",
    components: {
        ProfileSection,
        ActiveOrdersSection,
        OrdersHistorySection,
        TelegramSection,
    },
    data() {
        return {
            isInitialized: false,
            activeTab: "profile",
        };
    },
    computed: {
        ...mapStores(useAuthStore, useOrderStore),
    },
    async mounted() {
        this.isInitialized = true;
        if (this.authStore.isAuthenticated) {
            // Проверяем и загружаем данные профиля, если их еще нет
            if (!this.authStore.user) {
                await this.authStore.checkAuth();
            }
            // Загружаем больше заказов (50 - максимум API), чтобы показать активные и историю
            await this.orderStore.getClientOrders(this.authStore.token, 1, 50);
        }
    },
    methods: {
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        async handleLogout() {
            await this.authStore.logout();
            this.$router.push({ name: "home" });
        },
    },
};
</script>

<template>
    <div
        class="min-h-screen bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
    >
        <div
            class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-8 sm:py-12 lg:py-16"
        >
            <div
                v-if="authStore.isLoading && !isInitialized"
                class="flex items-center justify-center min-h-96"
            >
                <div class="text-center">
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#C20A6C] mx-auto mb-4"
                    ></div>
                    <p class="text-gray-600 dark:text-gray-400">
                        Загрузка данных клиента...
                    </p>
                </div>
            </div>

            <!-- Показываем ошибку если пользователь не авторизован -->
            <div v-else-if="!authStore.isAuthenticated" class="text-center">
                <div
                    class="bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-8 py-6 dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400"
                >
                    <h2 class="text-xl font-bold mb-2">Доступ запрещен</h2>
                    <p>Для доступа к этой странице необходимо авторизоваться</p>
                </div>
            </div>

            <!-- Личный кабинет -->
            <div v-else>
                <!-- Заголовок -->
                <div class="mb-8 sm:mb-12">
                    <div class="flex items-center justify-between mb-4">
                        <h1
                            class="text-3xl sm:text-4xl lg:text-5xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                        >
                            Личный кабинет
                        </h1>
                        <button
                            @click="handleLogout"
                            class="bg-[#C3006B] hover:bg-[#A8005A] text-white px-6 py-3 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50"
                        >
                            Выход
                        </button>
                    </div>
                    <div
                        class="h-px bg-dark-blue-500/30 dark:bg-dark-gray-200/90"
                    ></div>
                </div>

                <!-- Табы -->
                <div
                    class="flex gap-4 mb-8 sm:mb-12 bg-white/60 backdrop-blur-md p-2 border border-white/20 dark:bg-gray-800/60 dark:border-gray-700/20 overflow-x-auto"
                >
                    <button
                        @click="setActiveTab('profile')"
                        :class="[
                            'flex-1 px-6 py-4 font-jost-bold text-base sm:text-lg transition-all duration-300 whitespace-nowrap',
                            activeTab === 'profile'
                                ? 'bg-[#C3006B] text-white shadow-lg'
                                : 'text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80',
                        ]"
                    >
                        Профиль
                    </button>
                    <button
                        @click="setActiveTab('active')"
                        :class="[
                            'flex-1 px-6 py-4 font-jost-bold text-base sm:text-lg transition-all duration-300 whitespace-nowrap',
                            activeTab === 'active'
                                ? 'bg-[#C3006B] text-white shadow-lg'
                                : 'text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80',
                        ]"
                    >
                        Активные заказы
                    </button>
                    <button
                        @click="setActiveTab('history')"
                        :class="[
                            'flex-1 px-6 py-4 font-jost-bold text-base sm:text-lg transition-all duration-300 whitespace-nowrap',
                            activeTab === 'history'
                                ? 'bg-[#C3006B] text-white shadow-lg'
                                : 'text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80',
                        ]"
                    >
                        История заказов
                    </button>
                    <button
                        @click="setActiveTab('telegram')"
                        :class="[
                            'flex-1 px-6 py-4 font-jost-bold text-base sm:text-lg transition-all duration-300 whitespace-nowrap',
                            activeTab === 'telegram'
                                ? 'bg-[#C3006B] text-white shadow-lg'
                                : 'text-dark-gray-500 dark:text-gray-200 hover:bg-white/80 dark:hover:bg-gray-700/80',
                        ]"
                    >
                        Telegram
                    </button>
                </div>

                <!-- Контент табов -->
                <div>
                    <ProfileSection v-if="activeTab === 'profile'" />
                    <ActiveOrdersSection v-if="activeTab === 'active'" />
                    <OrdersHistorySection v-if="activeTab === 'history'" />
                    <TelegramSection v-if="activeTab === 'telegram'" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
