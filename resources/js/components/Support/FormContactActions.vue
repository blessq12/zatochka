<script>
import { mapState } from "pinia";
import { useBootstrapStore } from "../../stores/bootstrapStore.js";
import { socialIconKey } from "../../utils/socialIconKey.js";

export default {
    name: "FormContactActions",
    computed: {
        ...mapState(useBootstrapStore, ["phone", "phoneTel", "socialLinks"]),
        writeHref() {
            const links = this.socialLinks || [];
            const matchers = [
                (url, key) =>
                    key === "wa" ||
                    /whatsapp|wa\.me/i.test(url),
                (url, key) =>
                    key === "t" ||
                    /t\.me|telegram/i.test(url),
            ];

            for (const match of matchers) {
                const hit = links.find((link) =>
                    match(link.url || "", socialIconKey(link.url))
                );
                if (hit?.url) {
                    return hit.url;
                }
            }

            return links[0]?.url || "";
        },
    },
    async mounted() {
        await useBootstrapStore().fetchBootstrap();
    },
};
</script>

<template>
    <div
        class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center mb-8"
    >
        <a
            v-if="phoneTel"
            :href="`tel:${phoneTel}`"
            class="bg-white dark:bg-dark-blue-500 hover:bg-gray-100 dark:hover:bg-dark-blue-400 text-dark-blue-500 dark:text-white border-2 border-dark-blue-500 dark:border-dark-blue-400 px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform text-center"
        >
            ПОЗВОНИТЬ{{ phone ? `: ${phone}` : "" }}
        </a>
        <a
            v-if="writeHref"
            :href="writeHref"
            target="_blank"
            rel="noopener"
            class="bg-white dark:bg-dark-blue-500 hover:bg-gray-100 dark:hover:bg-dark-blue-400 text-dark-blue-500 dark:text-white border-2 border-dark-blue-500 dark:border-dark-blue-400 px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform text-center"
        >
            НАПИСАТЬ
        </a>
    </div>
</template>
