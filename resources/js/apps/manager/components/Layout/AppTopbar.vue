<script>
import { mapStores } from "pinia";
import { useManagerStore } from "../../stores/managerStore.js";

export default {
    name: "AppTopbar",
    computed: {
        ...mapStores(useManagerStore),
    },
    methods: {
        async logout() {
            await this.managerStore.logout();
            this.$router.push({ name: "manager.login" });
        },
    },
};
</script>

<template>
    <header class="h-14 border-b border-slate-200 bg-white flex items-center justify-between px-6">
        <div class="text-sm text-slate-500 font-jost-medium truncate">
            <slot />
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-slate-700 font-jost-medium">
                {{ managerStore.user?.name }}
            </span>
            <button
                type="button"
                class="text-sm font-jost-bold text-pink-500 hover:text-pink-600"
                @click="logout"
            >
                Выйти
            </button>
        </div>
    </header>
</template>
