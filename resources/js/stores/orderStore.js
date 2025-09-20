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
