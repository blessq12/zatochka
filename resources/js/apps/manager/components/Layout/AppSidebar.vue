<script>
import { navigationGroups } from "../../navigation.js";

export default {
    name: "AppSidebar",
    data() {
        return { groups: navigationGroups };
    },
    methods: {
        isActive(item) {
            return this.$route.path === item.to || this.$route.path.startsWith(item.to + "/");
        },
    },
};
</script>

<template>
    <aside class="w-64 shrink-0 bg-dark-blue-500 text-white min-h-screen flex flex-col">
        <div class="px-5 py-5 border-b border-white/10">
            <div class="font-jost-bold text-lg">Заточка.ТСК</div>
            <div class="text-xs text-white/70 mt-1">Панель менеджера</div>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-5">
            <div v-for="group in groups" :key="group.label">
                <div class="px-2 mb-2 text-[11px] uppercase tracking-wide text-white/50 font-jost-medium">
                    {{ group.label }}
                </div>
                <div class="space-y-1">
                    <router-link
                        v-for="item in group.items"
                        :key="item.to"
                        :to="item.to"
                        class="block px-3 py-2 text-sm font-jost-medium transition-colors"
                        :class="isActive(item) ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10'"
                    >
                        {{ item.label }}
                    </router-link>
                </div>
            </div>
        </nav>
    </aside>
</template>
