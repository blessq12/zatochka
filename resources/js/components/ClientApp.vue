<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";
import { useOrderStore } from "../stores/orderStore.js";
import AuthorizedApp from "./AuthorizedApp.vue";
import Auth from "./Auth/Auth.vue";

export default {
    name: "ClientApp",
    components: { AuthorizedApp, Auth },
    provide: {},
    data() {
        return {};
    },
    computed: {
        ...mapStores(useAuthStore, useOrderStore),
    },
    async mounted() {
        await this.authStore.checkAuth();
    },
};
</script>

<template>
    <div v-if="authStore.isAuthenticated">
        <AuthorizedApp />
    </div>
    <div v-else>
        <Auth />
    </div>
</template>

<style scoped></style>
