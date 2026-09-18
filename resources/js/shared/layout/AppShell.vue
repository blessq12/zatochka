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
    <div
        class="apps-shell flex min-h-0 w-full flex-1 overflow-hidden font-jost-regular"
    >
        <div class="hidden h-full shrink-0 lg:block">
            <AppSidebar :tagline="tagline" :items="items" />
        </div>

        <AppMobileDrawer
            :tagline="tagline"
            :items="items"
            :open="mobileOpen"
            @close="closeMobile"
        />

        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            <AppTopbar
                :user-name="userName"
                :user-email="userEmail"
                @logout="$emit('logout')"
                @toggle-mobile="toggleMobile"
            >
                <template #title>
                    <slot name="title" />
                </template>
            </AppTopbar>

            <main
                class="min-h-0 min-w-0 w-full flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain px-3 pt-3 pb-3 sm:px-5 sm:pt-4 sm:pb-4 lg:px-8 lg:pb-8 lg:pt-6"
            >
                <div class="mx-auto w-full max-w-[90rem]">
                    <slot />
                </div>
            </main>

            <AppBottomNav :items="bottomNavItems" />
        </div>
    </div>
</template>
