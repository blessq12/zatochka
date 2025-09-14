<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";
import { useOrderStore } from "../stores/orderStore.js";

export default {
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
    <div v-if="authStore.auth">
        <h1>Client App</h1>
    </div>
    <div v-else>
        <Auth />
    </div>
</template>

<style scoped></style>
