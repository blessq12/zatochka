<script>
import ClientMobileMenu from "./ClientMobileMenu.vue";
import ClientTopbar from "./ClientTopbar.vue";

export default {
    name: "ClientGuestShell",
    components: { ClientMobileMenu, ClientTopbar },
    data() {
        return {
            mobileOpen: false,
        };
    },
    computed: {
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
    },
};
</script>

<template>
    <div
        class="client-shell flex min-h-dvh w-full flex-col bg-white font-jost-regular dark:bg-dark-blue-500"
    >
        <ClientTopbar
            :title="pageTitle"
            :items="[]"
            :menu-open="mobileOpen"
            @toggle-mobile="toggleMobile"
        />

        <main
            class="min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain pb-[env(safe-area-inset-bottom)]"
        >
            <div class="mx-auto w-full max-w-lg px-3 py-4 sm:px-5 sm:py-5 lg:py-6">
                <router-view />
            </div>
        </main>

        <ClientMobileMenu
            :open="mobileOpen"
            :items="[]"
            :show-logout="false"
            @close="closeMobile"
        />
    </div>
</template>
