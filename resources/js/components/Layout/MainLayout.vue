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
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col">
        <Navigation />
        <main
            class="flex-1 container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-12 sm:py-16 lg:py-20"
        >
            <router-view />
        </main>
        <Footer />
    </div>
</template>

<style scoped></style>
