import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

export const useBootstrapStore = defineStore("bootstrap", {
    state: () => ({
        data: null,
        isLoading: false,
        error: null,
    }),

    getters: {
        isLoaded: (state) => state.data !== null,

        sharpeningBlocks(state) {
            return (state.data?.prices || [])
                .filter((block) => block.type === "sharpening")
                .map(({ title, items }) => ({ title, items }));
        },

        repairBlocks(state) {
            return (state.data?.prices || [])
                .filter((block) => block.type === "repair")
                .map(({ title, items }) => ({ title, items }));
        },

        contacts: (state) => state.data?.contacts ?? {},

        scheduleDays: (state) => state.data?.schedule?.days ?? [],

        deliveryInfo: (state) => state.data?.delivery_info ?? {},

        company: (state) => state.data?.company ?? {},

        faqItems: (state) => state.data?.faq?.items ?? [],
    },

    actions: {
        async fetchBootstrap() {
            if (this.data) {
                return { success: true, data: this.data };
            }

            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/bootstrap");
                this.data = response.data.data;
                return { success: true, data: this.data };
            } catch (error) {
                const message =
                    error.response?.data?.message ||
                    "Ошибка загрузки данных сайта";
                this.error = message;
                return { success: false, error: message };
            } finally {
                this.isLoading = false;
            }
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useBootstrapStore, import.meta.hot));
}
