<script>
import { mapState } from "pinia";
import PageHero from "../components/Layout/PageHero.vue";
import { useBootstrapStore } from "../stores/bootstrapStore.js";

export default {
    name: "ContactsPage",
    components: {
        PageHero,
    },
    computed: {
        ...mapState(useBootstrapStore, ["contacts", "isLoaded"]),
        contactPerson() {
            return this.contacts.contact_person || "";
        },
        phone() {
            return this.contacts.phone || "";
        },
        address() {
            return this.contacts.address || { main: "", details: [] };
        },
        socialEmail() {
            return this.contacts.social?.email || this.contacts.email || "";
        },
        socialLinks() {
            return this.contacts.social?.links || [];
        },
    },
    async mounted() {
        await useBootstrapStore().fetchBootstrap();
    },
};
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-dark-blue-500">
        <PageHero title="НАШИ КОНТАКТЫ">
            <div class="text-center space-y-4">
                <p
                    class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-white dark:text-white"
                >
                    {{ contactPerson }}
                </p>
                <p
                    class="text-xl sm:text-2xl lg:text-3xl font-jost-regular text-white dark:text-white"
                >
                    {{ phone }}
                </p>
            </div>
        </PageHero>

        <!-- Секция АДРЕС -->
        <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
            <div class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
                <div class="flex items-center justify-center space-x-3 mb-6 sm:mb-8">
                    <svg
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-6 h-6 sm:w-8 sm:h-8 text-pink-500 dark:text-pink-500"
                    >
                        <path
                            d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"
                            fill="currentColor"
                        />
                    </svg>
                    <h2
                        class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-pink-500 dark:text-pink-500 uppercase"
                    >
                        АДРЕС
                    </h2>
                </div>

                <p
                    class="text-xl sm:text-2xl lg:text-3xl font-jost-bold text-dark-blue-500 dark:text-white mb-4 sm:mb-6 text-center"
                >
                    {{ address.main }}
                </p>

                <div class="space-y-2 mb-8 sm:mb-12 text-center">
                    <p
                        v-for="(detail, index) in address.details"
                        :key="index"
                        class="text-base sm:text-lg lg:text-xl font-jost-regular text-dark-gray-500 dark:text-gray-200"
                    >
                        {{ detail }}
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row justify-center items-center gap-4 sm:gap-8">
                    <div
                        class="relative w-full sm:w-auto min-h-[200px] overflow-hidden shadow-2xl bg-gray-200 dark:bg-gray-700 aspect-[4/3] bg-cover bg-center bg-no-repeat max-w-md"
                        :style="{
                            backgroundImage: `url('/images/entrance.png')`,
                        }"
                    ></div>

                    <div
                        class="relative w-full sm:w-auto min-h-[200px] overflow-hidden shadow-2xl bg-gray-200 dark:bg-gray-700 aspect-[4/3] bg-cover bg-center bg-no-repeat max-w-md"
                        :style="{ backgroundImage: `url('/images/map.png')` }"
                    ></div>
                </div>
            </div>
        </section>

        <!-- Секция МЫ В СОЦИАЛЬНЫХ СЕТЯХ -->
        <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
            <div class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
                <h2
                    class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-pink-500 dark:text-pink-500 uppercase mb-8 sm:mb-12 text-center"
                >
                    МЫ В СОЦИАЛЬНЫХ СЕТЯХ
                </h2>

                <div
                    class="flex justify-center items-center space-x-6 sm:space-x-8 mb-8 sm:mb-12"
                >
                    <a
                        v-for="link in socialLinks"
                        :key="link.name"
                        :href="link.url"
                        target="_blank"
                        rel="noopener"
                        class="w-16 h-16 sm:w-20 sm:h-20 bg-dark-blue-500 dark:bg-dark-blue-600 rounded-full flex items-center justify-center hover:bg-dark-blue-600 dark:hover:bg-dark-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl"
                        :aria-label="link.name"
                    >
                        <span class="text-white text-xs sm:text-sm font-jost-bold">{{
                            link.icon
                        }}</span>
                    </a>
                </div>

                <p
                    class="text-xl sm:text-2xl lg:text-3xl font-jost-regular text-dark-blue-500 dark:text-white text-center mb-12 sm:mb-16"
                >
                    {{ socialEmail }}
                </p>
            </div>
        </section>
    </div>
</template>

<style scoped></style>
