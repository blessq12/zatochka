import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

export const usePriceStore = defineStore("price", {
    state: () => ({
        bootstrap: null,
    }),

    getters: {
        sharpeningBlocks(state) {
            return (state.bootstrap?.prices || [])
                .filter((block) => block.type === "sharpening")
                .map(({ title, items }) => ({ title, items }));
        },

        repairBlocks(state) {
            return (state.bootstrap?.prices || [])
                .filter((block) => block.type === "repair")
                .map(({ title, items }) => ({ title, items }));
        },
    },

    actions: {
        async fetchBootstrap() {
            if (this.bootstrap) {
                return { success: true, data: this.bootstrap };
            }

            try {
                const response = await axios.get("/api/bootstrap");
                this.bootstrap = response.data.data;
                return { success: true, data: this.bootstrap };
            } catch (error) {
                const message =
                    error.response?.data?.message ||
                    "Ошибка загрузки данных сайта";
                return { success: false, error: message };
            }
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(usePriceStore, import.meta.hot));
}
