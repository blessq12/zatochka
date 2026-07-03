<script>
import MainLayout from "./components/Layout/MainLayout.vue";
import { useBootstrapStore } from "./stores/bootstrapStore.js";
import { useRoute } from "vue-router";

export default {
    name: "App",
    components: {
        MainLayout,
    },
    setup() {
        const route = useRoute();
        return {
            route,
        };
    },
    computed: {
        isPosRoute() {
            return this.route.path.startsWith("/pos");
        },
    },
    async mounted() {
        if (!this.isPosRoute) {
            const bootstrapStore = useBootstrapStore();
            await bootstrapStore.fetchBootstrap();
        }
    },
};
</script>

<template>
    <MainLayout v-if="!isPosRoute" />
    <router-view v-else />
</template>

<style scoped></style>
