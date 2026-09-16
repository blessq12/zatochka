<script>
import AppSidebar from "./AppSidebar.vue";
import AppTopbar from "./AppTopbar.vue";

export default {
    name: "AppShell",
    components: { AppSidebar, AppTopbar },
    props: {
        tagline: { type: String, default: "" },
        items: { type: Array, default: () => [] },
        userName: { type: String, default: "" },
        userEmail: { type: String, default: "" },
    },
    emits: ["logout"],
    data() {
        return { mobileOpen: false };
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
    <div class="flex min-h-screen bg-slate-50 font-jost-regular">
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-[998] bg-black/50 lg:hidden"
            @click="closeMobile"
        />

        <AppSidebar
            :tagline="tagline"
            :items="items"
            :mobile-open="mobileOpen"
            @close="closeMobile"
        />

        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
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

            <main class="min-w-0 flex-1 overflow-auto p-3 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
