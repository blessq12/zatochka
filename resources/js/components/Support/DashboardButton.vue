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
};
</script>

<template>
    <router-link
        v-if="isAuthenticated"
        to="/client/dashboard"
        :title="'Перейти в дашборд'"
        class="bg-[#003859] hover:bg-[#002c4e] text-white px-6 py-3 font-jost-bold text-base transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-[#C20A6C] inline-block text-center"
    >
        В панель
    </router-link>
    <router-link
        v-else
        to="/client/dashboard"
        :title="'Войти в систему'"
        class="bg-[#003859] hover:bg-[#002c4e] text-white px-6 py-3 font-jost-bold text-base transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-[#C20A6C] inline-block text-center"
    >
        Войти
    </router-link>
</template>

<style scoped></style>
