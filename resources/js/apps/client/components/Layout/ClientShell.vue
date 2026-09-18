<script>
import AppShell from "@shared/layout/AppShell.vue";
import { mapStores } from "pinia";
import { bottomNavItems, navigationItems } from "../../navigation.js";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "ClientShell",
    components: { AppShell },
    computed: {
        ...mapStores(useAuthStore),
        items() {
            return navigationItems;
        },
        bottomItems() {
            return bottomNavItems;
        },
    },
    methods: {
        async logout() {
            await this.authStore.logout();
            this.$router.push({ name: "client.login" });
        },
    },
};
</script>

<template>
    <AppShell
        tagline="Кабинет клиента"
        :items="items"
        :bottom-items="bottomItems"
        :user-name="authStore.user?.full_name || ''"
        :user-email="authStore.user?.email || ''"
        @logout="logout"
    >
        <template #title>
            {{ $route.meta.title || "Клиент" }}
        </template>
        <router-view />
    </AppShell>
</template>
