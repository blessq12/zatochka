<script>
import AppShell from "@shared/layout/AppShell.vue";
import { mapStores } from "pinia";
import { navigationItems } from "../../navigation.js";
import { useManagerStore } from "../../stores/managerStore.js";

export default {
    name: "ManagerShell",
    components: { AppShell },
    computed: {
        ...mapStores(useManagerStore),
        items() {
            return navigationItems;
        },
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
    <AppShell
        tagline="Панель менеджера"
        :items="items"
        :user-name="''"
        :user-email="managerStore.user?.email || ''"
        @logout="logout"
    >
        <template #title>
            {{ $route.meta.title || "Менеджер" }}
        </template>
        <router-view />
    </AppShell>
</template>
