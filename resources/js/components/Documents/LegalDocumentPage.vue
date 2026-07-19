<script>
import axios from "axios";

export default {
    name: "LegalDocumentPage",
    props: {
        slug: {
            type: String,
            required: true,
        },
        fallbackTitle: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            title: this.fallbackTitle,
            bodyHtml: "",
            loading: true,
            error: "",
        };
    },
    async mounted() {
        await this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = "";
            try {
                const { data } = await axios.get(`/api/documents/${this.slug}`);
                const doc = data.data || data;
                this.title = doc.title || this.fallbackTitle;
                this.bodyHtml = doc.body_html || doc.bodyHtml || "";
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    "Не удалось загрузить документ";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <div
        class="min-h-screen bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20"
    >
        <div class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
            <h1
                class="text-4xl font-jost-bold text-dark-blue-500 dark:text-white"
            >
                {{ title }}
            </h1>
            <p
                v-if="loading"
                class="text-xl text-dark-gray-500 dark:text-gray-200 mt-4"
            >
                Загрузка…
            </p>
            <p
                v-else-if="error"
                class="text-xl text-red-600 dark:text-red-400 mt-4"
            >
                {{ error }}
            </p>
            <div
                v-else
                class="prose prose-lg max-w-none mt-8 text-dark-gray-500 dark:text-gray-200 dark:prose-invert"
                v-html="bodyHtml"
            ></div>
        </div>
    </div>
</template>
