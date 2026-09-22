<script>
import AppShell from "@shared/layout/AppShell.vue";
import { mapStores } from "pinia";
import { bottomNavItems, navigationItems } from "../../navigation.js";
import { useManagerStore } from "../../stores/managerStore.js";
import ManagerGlobalSearch from "./ManagerGlobalSearch.vue";

export default {
    name: "ManagerShell",
    components: { AppShell, ManagerGlobalSearch },
    computed: {
        ...mapStores(useManagerStore),
        items() {
            return navigationItems;
        },
        bottomItems() {
            return bottomNavItems;
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
        :bottom-items="bottomItems"
        :user-name="''"
        :user-email="managerStore.user?.email || ''"
        @logout="logout"
    >
        <template #title>
            {{ $route.meta.title || "Менеджер" }}
        </template>
        <template #search>
            <ManagerGlobalSearch />
        </template>
        <router-view />
    </AppShell>
</template>
