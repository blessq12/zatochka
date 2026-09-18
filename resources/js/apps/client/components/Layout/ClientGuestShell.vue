<script>
import ClientTopbar from "./ClientTopbar.vue";

export default {
    name: "ClientGuestShell",
    components: { ClientTopbar },
    computed: {
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
        <ClientTopbar :title="pageTitle" :items="[]" />

        <main
            class="min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain"
        >
            <div class="mx-auto w-full max-w-lg px-3 py-3 sm:px-5 lg:py-6">
                <router-view />
            </div>
        </main>
    </div>
</template>
