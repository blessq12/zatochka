<script>
import Footer from "./Footer.vue";
import Navigation from "./Navigation.vue";

export default {
    name: "MainLayout",
    components: {
        Navigation,
        Footer,
    },
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-dark-blue-500 flex flex-col">
        <Navigation />
        <main class="container mx-auto">
            <router-view />
        </main>
        <Footer />
    </div>
</template>

<style scoped></style>
