<script>
import { mapStores } from "pinia";
import { bottomNavItems, navigationItems } from "../../navigation.js";
import { useAuthStore } from "../../stores/authStore.js";
import ClientBottomNav from "./ClientBottomNav.vue";
import ClientTopbar from "./ClientTopbar.vue";

export default {
    name: "ClientShell",
    components: {
        ClientBottomNav,
        ClientTopbar,
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
};
</script>

<template>
    <div
        class="client-shell flex min-h-0 w-full flex-1 flex-col overflow-hidden bg-white font-jost-regular dark:bg-dark-blue-500"
    >
        <ClientTopbar :title="pageTitle" :items="items" />

        <main
            class="min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain"
        >
            <div class="mx-auto w-full max-w-[90rem] px-3 py-3 sm:px-5 lg:px-8 lg:py-4">
                <router-view />
            </div>
        </main>

        <ClientBottomNav :items="bottomItems" />
    </div>
</template>
