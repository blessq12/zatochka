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
        class="client-shell flex min-h-dvh w-full flex-col bg-white font-jost-regular dark:bg-dark-blue-500"
    >
        <ClientTopbar
            :title="pageTitle"
            :items="items"
            :menu-open="mobileOpen"
            @logout="logout"
            @toggle-mobile="toggleMobile"
        />

        <main
            class="min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain pb-[calc(5.5rem+env(safe-area-inset-bottom))] lg:pb-0"
        >
            <div class="mx-auto w-full max-w-[90rem] px-3 py-4 sm:px-5 sm:py-5 lg:px-8 lg:py-6">
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
