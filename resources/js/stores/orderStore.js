import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

export const useOrderStore = defineStore("order", {
    state: () => ({
        orders: [],
        currentOrder: null,
        isLoading: false,
        error: null,
    }),

    actions: {
        async getClientOrders(token) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/client/orders-get", {
                    headers: { Authorization: `Bearer ${token}` },
                });

                this.orders = response.data.orders || [];
                return { success: true, data: response.data };
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Ошибка получения заказов";
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useOrderStore, import.meta.hot));
}
