<script>
import { navigationItems } from "../navigation.js";

export default {
    name: "DashboardPage",
    data() {
        return { items: navigationItems };
    },
};
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-jost-bold text-dark-blue-500">Дашборд</h1>
        <p class="text-slate-600">Выберите раздел в меню слева.</p>
        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
            <router-link
                v-for="item in items"
                :key="item.to"
                :to="item.to"
                class="border border-slate-200 bg-white px-4 py-3 text-sm font-jost-medium text-pink-500 hover:border-pink-300 hover:bg-pink-50"
            >
                {{ item.label }}
            </router-link>
        </div>
    </div>
</template>
