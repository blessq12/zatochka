import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";
import createLeadRequestDto from "../dto/form/leadRequestDto.js";
import { toastService } from "../services/toastService.js";

export const useOrderStore = defineStore("order", {
    state: () => ({
        activeOrders: [],
        historyOrders: [],
        isLoadingActive: false,
        isLoadingHistory: false,
        submitLeadLoading: false,
        historyPagination: {
            total: 0,
            page: 1,
            per_page: 10,
        },
    }),

    actions: {
        async submitLead(formData, serviceType = "sharpening") {
            this.submitLeadLoading = true;

            try {
                const payload = createLeadRequestDto({
                    serviceType,
                    formData,
                });

                const response = await axios.post("/api/leads", payload);

                toastService.success(
                    response.data.data?.message ||
                        "Заявка принята. Менеджер свяжется с вами."
                );
                return { success: true, data: response.data };
            } catch (error) {
                const message =
                    error.response?.data?.message || "Ошибка отправки заявки";
                toastService.error(message);
                return { success: false, error: message };
            } finally {
                this.submitLeadLoading = false;
            }
        },

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

        async createReview(orderId, rating, comment) {
            try {
                const response = await axios.post(
                    `/api/client/orders/${orderId}/review`,
                    { rating, comment }
                );

                const order = this.historyOrders.find(
                    (item) => item.id === orderId
                );
                if (order) {
                    order.review_exists = true;
                    order.review_status = response.data.data?.status;
                }

                toastService.success(
                    "Отзыв отправлен и ожидает модерации!"
                );
                return { success: true, data: response.data };
            } catch (error) {
                const message =
                    error.response?.data?.message || "Ошибка создания отзыва";
                toastService.error(message);
                return { success: false, error: message };
            }
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useOrderStore, import.meta.hot));
}
