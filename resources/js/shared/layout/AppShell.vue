<script>
import AppBottomNav from "./AppBottomNav.vue";
import AppMobileDrawer from "./AppMobileDrawer.vue";
import AppSidebar from "./AppSidebar.vue";
import AppTopbar from "./AppTopbar.vue";

export default {
    name: "AppShell",
    components: { AppBottomNav, AppMobileDrawer, AppSidebar, AppTopbar },
    props: {
        tagline: { type: String, default: "" },
        items: { type: Array, default: () => [] },
        bottomItems: { type: Array, default: null },
        userName: { type: String, default: "" },
        userEmail: { type: String, default: "" },
    },
    emits: ["logout"],
    data() {
        return { mobileOpen: false };
    },
    computed: {
        bottomNavItems() {
            return this.bottomItems?.length ? this.bottomItems : this.items;
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
    },
};
</script>

<template>
    <div class="apps-shell flex min-h-dvh w-full font-jost-regular">
        <div class="hidden shrink-0 lg:block">
            <AppSidebar :tagline="tagline" :items="items" />
        </div>

        <AppMobileDrawer
            :tagline="tagline"
            :items="items"
            :open="mobileOpen"
            @close="closeMobile"
        />

        <div class="flex min-h-dvh min-w-0 flex-1 flex-col">
            <AppTopbar
                :user-name="userName"
                :user-email="userEmail"
                @logout="$emit('logout')"
                @toggle-mobile="toggleMobile"
            >
                <template #title>
                    <slot name="title" />
                </template>
                <template #search>
                    <slot name="search" />
                </template>
            </AppTopbar>

            <main
                class="min-w-0 w-full flex-1 overflow-auto px-3 pb-[calc(5.5rem+env(safe-area-inset-bottom))] sm:px-5 lg:px-8 lg:pb-8"
            >
                <div class="mx-auto w-full max-w-[90rem] py-4 sm:py-5 lg:py-6">
                    <slot />
                </div>
            </main>

            <AppBottomNav :items="bottomNavItems" />
        </div>
    </div>
</template>
