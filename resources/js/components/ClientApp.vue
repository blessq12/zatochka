<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";
import { useOrderStore } from "../stores/orderStore.js";
import AuthorizedApp from "./AuthorizedApp.vue";

export default {
    components: { AuthorizedApp },
    provide: {},
    data() {
        return {};
    },
    computed: {
        ...mapStores(useAuthStore, useOrderStore),
    },
};
</script>

<template>
    <button @click="authStore.auth = !authStore.auth">Auth</button>
    <div v-if="authStore.auth">
        <AuthorizedApp />
    </div>
    <div v-else>
        <Auth />
    </div>
</template>

<style scoped></style>
