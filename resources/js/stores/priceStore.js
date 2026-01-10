import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

export const usePriceStore = defineStore("price", {
    state: () => ({
        sharpeningBlocks: [],
        repairBlocks: [],
        isLoading: false,
        error: null,
    }),

    actions: {
        async fetchSharpeningPrices() {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/prices/sharpening");
                this.sharpeningBlocks = response.data.priceBlocks || [];
                return { success: true, data: response.data };
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Ошибка загрузки прайс-листа";
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },

        async fetchRepairPrices() {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/prices/repair");
                this.repairBlocks = response.data.priceBlocks || [];
                return { success: true, data: response.data };
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Ошибка загрузки прайс-листа";
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },

        async fetchAllPrices() {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/prices/all");
                this.sharpeningBlocks = response.data.sharpeningBlocks || [];
                this.repairBlocks = response.data.repairBlocks || [];
                return { success: true, data: response.data };
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Ошибка загрузки прайс-листа";
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(usePriceStore, import.meta.hot));
}
