<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "DashboardButton",
    computed: {
        ...mapStores(useAuthStore),

        isAuthenticated() {
            return this.authStore.isAuthenticated;
        },
    },
    mounted() {
        this.authStore.checkAuth();
    },
    methods: {
        handleClick() {
            window.location.href = "/client/dashboard";
        },
    },
};
</script>

<template>
    <button
        @click="handleClick"
        :title="isAuthenticated ? 'Перейти в дашборд' : 'Войти в систему'"
        class="bg-blue-600/90 backdrop-blur-xs hover:bg-blue-700/90 text-white px-8 py-4 rounded-2xl font-jost-bold text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-110 transform focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 dark:bg-blue-500/90 dark:hover:bg-blue-600/90 dark:backdrop-blur-xs"
    >
        {{ this.isAuthenticated ? "В панель" : "Войти" }}
    </button>
</template>

<style scoped></style>
