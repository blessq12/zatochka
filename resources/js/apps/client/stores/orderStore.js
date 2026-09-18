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
        async fetchActiveOrders() {
            this.isLoadingActive = true;

            try {
                const response = await axios.get("/api/orders", {
                    params: { scope: "active" },
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

        async fetchHistoryOrders() {
            this.isLoadingHistory = true;

            try {
                const response = await axios.get("/api/orders", {
                    params: { scope: "archive" },
                });

                const rows = response.data.data || [];
                this.historyOrders = rows;
                this.historyPagination = {
                    total: rows.length,
                    page: 1,
                    per_page: rows.length || 10,
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

        async createOrder(payload) {
            const { data } = await axios.post("/api/orders", payload);
            await this.fetchActiveOrders();
            return data;
        },

        async submitReview(orderId, { rating, text = null }) {
            const { data } = await axios.post(`/api/orders/${orderId}/review`, {
                rating,
                text,
            });
            await this.fetchHistoryOrders();
            return data;
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useOrderStore, import.meta.hot));
}
