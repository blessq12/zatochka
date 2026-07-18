<script>
import { mapState } from "pinia";
import { useBootstrapStore } from "../../stores/bootstrapStore.js";

export default {
    name: "Footer",
    computed: {
        ...mapState(useBootstrapStore, [
            "company",
            "phone",
            "phoneTel",
            "socialLinks",
        ]),
    },
    async mounted() {
        await useBootstrapStore().fetchBootstrap();
    },
};
</script>

<template>
    <footer
        class="bg-white/85 backdrop-blur-2xl border-t border-white/25 dark:bg-gray-800/85 dark:backdrop-blur-2xl dark:border-gray-700/25 mt-auto"
    >
        <div
            class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-8 sm:py-10 lg:py-12"
        >
            <div class="flex justify-center mb-6">
                <a
                    v-if="phoneTel"
                    :href="`tel:${phoneTel}`"
                    class="text-xl sm:text-2xl font-jost-bold text-dark-blue-500 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-300"
                >
                    {{ phone }}
                </a>
            </div>

            <div
                v-if="socialLinks.length"
                class="flex justify-center items-center gap-4 mb-6 flex-wrap"
            >
                <a
                    v-for="link in socialLinks"
                    :key="link.name"
                    :href="link.url"
                    target="_blank"
                    rel="noopener"
                    class="text-sm font-jost-medium text-dark-blue-500 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-300"
                >
                    {{ link.name }}
                </a>
            </div>

            <div
                class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 mb-6"
            >
                <router-link
                    to="/privacy-policy"
                    class="text-sm font-jost-medium text-dark-gray-500 hover:text-blue-500 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-300"
                >
                    Политика конфиденциальности
                </router-link>
                <span
                    class="hidden sm:inline text-dark-gray-500 dark:text-gray-400"
                >
                    |
                </span>
                <router-link
                    to="/terms-of-service"
                    class="text-sm font-jost-medium text-dark-gray-500 hover:text-blue-500 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-300"
                >
                    Правила пользования
                </router-link>
            </div>

            <div
                v-if="company.owner_name"
                class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 text-xs sm:text-sm font-jost-regular text-gray-500 dark:text-gray-400 mb-4"
            >
                <p>{{ company.owner_name }}</p>
                <span v-if="company.inn" class="hidden sm:inline">•</span>
                <p v-if="company.inn">ИНН: {{ company.inn }}</p>
                <span v-if="company.ogrn" class="hidden sm:inline">•</span>
                <p v-if="company.ogrn">ОГРН: {{ company.ogrn }}</p>
            </div>

            <div
                class="flex flex-col items-center justify-center gap-2 sm:gap-3 text-xs sm:text-sm font-jost-regular text-gray-500 dark:text-gray-400"
            >
                <p v-if="company.legal_address">
                    <span class="font-jost-medium">Юридический адрес:</span>
                    {{ company.legal_address }}
                </p>
                <p v-if="company.actual_address">
                    <span class="font-jost-medium">Фактический адрес:</span>
                    {{ company.actual_address }}
                </p>
            </div>
        </div>
    </footer>
</template>

<style scoped></style>
