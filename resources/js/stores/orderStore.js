import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";
import createOrderRequestDto from "../dto/form/orderRequestDto.js";
import createReviewRequestDto from "../dto/review/createReviewRequestDto.js";
import { toastService } from "../services/toastService.js";
import { useAuthStore } from "./authStore.js";

export const useOrderStore = defineStore("order", {
    state: () => ({
        orders: [],
        currentOrder: null,
        isLoading: false,
        error: null,
        createOrderLoading: false,
        createOrderError: null,
        createReviewLoading: false,
        createReviewError: null,
        getReviewLoading: false,
        getReviewError: null,
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

                // Проверяем, авторизован ли клиент, и добавляем токен в заголовки
                const authStore = useAuthStore();
                const headers = {};
                if (authStore.isAuthenticated && authStore.token) {
                    headers.Authorization = `Bearer ${authStore.token}`;
                }

                const response = await axios.post("/api/order/create", payload, {
                    headers,
                });

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

        async createReview(token, orderId, rating, comment) {
            this.createReviewLoading = true;
            this.createReviewError = null;

            try {
                const payload = createReviewRequestDto({
                    orderId,
                    rating,
                    comment,
                });

                const response = await axios.post(
                    "/api/review/create",
                    payload,
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    }
                );

                toastService.success(
                    "Отзыв успешно создан и отправлен на модерацию!"
                );
                return { success: true, data: response.data };
            } catch (error) {
                this.createReviewError =
                    error.response?.data?.message || "Ошибка создания отзыва";
                toastService.error(this.createReviewError);
                return { success: false, error: this.createReviewError };
            } finally {
                this.createReviewLoading = false;
            }
        },

        async getOrderReview(token, orderId) {
            this.getReviewLoading = true;
            this.getReviewError = null;

            try {
                const response = await axios.get(
                    `/api/review/order/${orderId}`,
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    }
                );

                return { success: true, data: response.data };
            } catch (error) {
                this.getReviewError =
                    error.response?.data?.message || "Ошибка получения отзыва";
                return { success: false, error: this.getReviewError };
            } finally {
                this.getReviewLoading = false;
            }
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useOrderStore, import.meta.hot));
}
