import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";
import createOrderRequestDto from "../dto/form/orderRequestDto.js";
import { toastService } from "../services/toastService.js";

export const useOrderStore = defineStore("order", {
    state: () => ({
        orders: [],
        currentOrder: null,
        isLoading: false,
        error: null,
        createOrderLoading: false,
        createOrderError: null,
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
            has_more_pages: false,
        },
    }),

    actions: {
        async createOrder(formData, serviceType = "sharpening") {
            this.createOrderLoading = true;
            this.createOrderError = null;

            try {
                const payload = createOrderRequestDto({
                    serviceType,
                    formData,
                });

                const response = await axios.post("/api/order/create", payload);

                toastService.success("Заказ успешно создан!");
                return { success: true, data: response.data };
            } catch (error) {
                this.createOrderError =
                    error.response?.data?.message || "Ошибка создания заказа";
                toastService.error(this.createOrderError);
                return { success: false, error: this.createOrderError };
            } finally {
                this.createOrderLoading = false;
            }
        },

        async getClientOrders(token, page = 1, perPage = 10) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/client/orders-get", {
                    headers: { Authorization: `Bearer ${token}` },
                    params: { page, per_page: perPage },
                });

                this.orders = response.data.orders || [];
                this.pagination = response.data.pagination || {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0,
                    has_more_pages: false,
                };
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
