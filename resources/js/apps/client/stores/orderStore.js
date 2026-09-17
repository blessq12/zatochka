import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

export const useOrderStore = defineStore("order", {
    state: () => ({
        activeOrders: [],
        historyOrders: [],
        isLoadingActive: false,
        isLoadingHistory: false,
        historyPagination: {
            total: 0,
            page: 1,
            per_page: 10,
        },
    }),

    actions: {
        async fetchActiveOrders(page = 1, perPage = 20) {
            this.isLoadingActive = true;

            try {
                const response = await axios.get("/api/client/orders/active", {
                    params: { page, per_page: perPage },
                });

                this.activeOrders = response.data.data || [];

                return { success: true, data: response.data };
            } catch (error) {
                const message =
                    error.response?.data?.message ||
                    "Ошибка получения активных заказов";
                return { success: false, error: message };
            } finally {
                this.isLoadingActive = false;
            }
        },

        async fetchHistoryOrders(page = 1, perPage = 10) {
            this.isLoadingHistory = true;

            try {
                const response = await axios.get("/api/client/orders/history", {
                    params: { page, per_page: perPage },
                });

                this.historyOrders = response.data.data || [];
                this.historyPagination = {
                    total: response.data.meta?.total ?? 0,
                    page: response.data.meta?.page ?? page,
                    per_page: response.data.meta?.per_page ?? perPage,
                };

                return { success: true, data: response.data };
            } catch (error) {
                const message =
                    error.response?.data?.message ||
                    "Ошибка получения истории заказов";
                return { success: false, error: message };
            } finally {
                this.isLoadingHistory = false;
            }
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useOrderStore, import.meta.hot));
}
