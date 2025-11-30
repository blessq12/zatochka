<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";
import { useOrderStore } from "../stores/orderStore.js";
import AuthorizedApp from "./AuthorizedApp.vue";

export default {
    name: "ClientApp",
    components: { AuthorizedApp },
    provide: {},
    data() {
        return {};
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
    <div v-else>
        <div class="min-h-screen bg-white dark:bg-dark-blue-500 flex items-center justify-center">
            <div class="text-center px-8">
                <h1 class="text-2xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-4">
                    Требуется авторизация
                </h1>
                <p class="text-lg font-jost-regular text-dark-gray-500 dark:text-gray-200">
                    Пожалуйста, войдите в систему
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
