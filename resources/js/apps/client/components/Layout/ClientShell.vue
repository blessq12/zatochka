<script>
import { mapStores } from "pinia";
import { bottomNavItems, navigationItems } from "../../navigation.js";
import { useAuthStore } from "../../stores/authStore.js";
import ClientBottomNav from "./ClientBottomNav.vue";
import ClientHeader from "./ClientHeader.vue";
import ClientMobileMenu from "./ClientMobileMenu.vue";
import ClientNavTabs from "./ClientNavTabs.vue";

export default {
    name: "ClientShell",
    components: {
        ClientBottomNav,
        ClientHeader,
        ClientMobileMenu,
        ClientNavTabs,
    },
    data() {
        return {
            mobileOpen: false,
        };
    },
    computed: {
        ...mapStores(useAuthStore),
        items() {
            return navigationItems;
        },
        bottomItems() {
            return bottomNavItems;
        },
    },
    watch: {
        $route() {
            this.mobileOpen = false;
        },
    },
    methods: {
        toggleMobile() {
            this.mobileOpen = !this.mobileOpen;
        },
        closeMobile() {
            this.mobileOpen = false;
        },
        async logout() {
            await this.authStore.logout();
            this.$router.push({ name: "client.login" });
        },
    },
};
</script>

<template>
    <div
        class="client-shell min-h-dvh w-full bg-white/80 font-jost-regular backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
    >
        <div
            class="container mx-auto px-4 py-6 sm:px-8 sm:py-10 lg:px-16 lg:py-12 xl:px-20"
        >
            <ClientHeader
                @logout="logout"
                @toggle-mobile="toggleMobile"
            />

            <ClientNavTabs :items="items" />

            <main
                class="min-w-0 pb-[calc(4.25rem+env(safe-area-inset-bottom))] lg:pb-0"
            >
                <router-view />
            </main>
        </div>

        <ClientMobileMenu
            :open="mobileOpen"
            :items="items"
            @close="closeMobile"
            @logout="logout"
        />

        <ClientBottomNav :items="bottomItems" />
    </div>
</template>
