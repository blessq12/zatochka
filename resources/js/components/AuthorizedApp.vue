<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";
import { useOrderStore } from "../stores/orderStore.js";

export default {
    name: "AuthorizedApp",

    data() {
        return {
            isInitialized: false,
        };
    },
    computed: {
        ...mapStores(useAuthStore, useOrderStore),
    },

    async mounted() {
        this.isInitialized = true;
        await this.orderStore.getClientOrders(this.authStore.token);
        console.log(this.orderStore.orders);
        console.log(this.authStore.user);
        console.log(this.authStore.bonusAccount);
    },
};
</script>

<template>
    <div
        class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-8 sm:py-12 lg:py-16"
    >
        <div
            v-if="authStore.isLoading"
            class="flex items-center justify-center min-h-96"
        >
            <div class="text-center">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"
                ></div>
                <p class="text-gray-600 dark:text-gray-400">
                    Загрузка данных клиента...
                </p>
            </div>
        </div>

        <!-- Показываем ошибку если пользователь не авторизован -->
        <div v-else-if="!authStore.isAuthenticated" class="text-center">
            <div
                class="bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-8 py-6 rounded-2xl dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400"
            >
                <h2 class="text-xl font-bold mb-2">Доступ запрещен</h2>
                <p>Для доступа к этой странице необходимо авторизоваться</p>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
