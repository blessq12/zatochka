<script>
import MainLayout from "./components/Layout/MainLayout.vue";
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
};
</script>

<template>
    <MainLayout v-if="!isPosRoute" />
    <router-view v-else />
</template>

<style scoped></style>
