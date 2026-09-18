<script>
import { mapStores } from "pinia";
import { bottomNavItems, navigationItems } from "../../navigation.js";
import { useAuthStore } from "../../stores/authStore.js";
import ClientBottomNav from "./ClientBottomNav.vue";
import ClientMobileMenu from "./ClientMobileMenu.vue";
import ClientTopbar from "./ClientTopbar.vue";

export default {
    name: "ClientShell",
    components: {
        ClientBottomNav,
        ClientMobileMenu,
        ClientTopbar,
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
        pageTitle() {
            return this.$route.meta.title || "Кабинет";
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
        class="client-shell flex h-full max-h-full w-full flex-col overflow-hidden bg-white font-jost-regular dark:bg-dark-blue-500"
    >
        <ClientTopbar
            :title="pageTitle"
            :items="items"
            @logout="logout"
            @toggle-mobile="toggleMobile"
        />

        <main
            class="min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain pb-[calc(4.5rem+env(safe-area-inset-bottom))] lg:pb-0"
        >
            <div
                class="container mx-auto px-4 pt-6 pb-4 sm:px-8 sm:py-10 lg:px-16 lg:py-12 xl:px-20"
            >
                <router-view />
            </div>
        </main>

        <ClientMobileMenu
            :open="mobileOpen"
            :items="items"
            @close="closeMobile"
            @logout="logout"
        />

        <ClientBottomNav :items="bottomItems" />
    </div>
</template>
