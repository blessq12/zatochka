<script>
import MainLayout from "./components/Layout/MainLayout.vue";
import { usePriceStore } from "./stores/priceStore.js";
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
            const priceStore = usePriceStore();
            await priceStore.fetchBootstrap();
        }
    },
};
</script>

<template>
    <MainLayout v-if="!isPosRoute" />
    <router-view v-else />
</template>

<style scoped></style>
