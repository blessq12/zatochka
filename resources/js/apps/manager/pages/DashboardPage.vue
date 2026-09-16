<script>
import ManagerLayout from "../components/Layout/ManagerLayout.vue";
import { navigationGroups } from "../navigation.js";

export default {
    name: "DashboardPage",
    components: { ManagerLayout },
    data() {
        return { groups: navigationGroups };
    },
};
</script>

<template>
    <ManagerLayout>
        <template #title>Дашборд</template>
        <div class="space-y-6">
            <h1 class="text-2xl font-jost-bold text-dark-blue-500">Дашборд</h1>
            <p class="text-slate-600">Выберите раздел в меню слева.</p>
            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                <div
                    v-for="group in groups"
                    :key="group.label"
                    class="bg-white border border-slate-200 p-5"
                >
                    <div class="font-jost-bold text-dark-blue-500 mb-3">{{ group.label }}</div>
                    <div class="space-y-2">
                        <router-link
                            v-for="item in group.items"
                            :key="item.to"
                            :to="item.to"
                            class="block text-sm text-pink-500 hover:text-pink-600 font-jost-medium"
                        >
                            {{ item.label }}
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>
