import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";
import createPublicOrderRequestDto from "../dto/form/publicOrderRequestDto.js";
import { toastService } from "@shared/toastService.js";

export const useOrderStore = defineStore("order", {
    state: () => ({
        submitOrderLoading: false,
    }),

    actions: {
        async createPublicOrder(formData, serviceType = "sharpening") {
            this.submitOrderLoading = true;

            try {
                const payload = createPublicOrderRequestDto({
                    serviceType,
                    formData,
                });

                const response = await axios.post("/api/public/orders", payload);

                toastService.success(
                    response.data.data?.message ||
                        "Заказ создан. Менеджер свяжется с вами."
                );
                return { success: true, data: response.data };
            } catch (error) {
                const message =
                    error.response?.data?.message || "Ошибка создания заказа";
                toastService.error(message);
                return { success: false, error: message };
            } finally {
                this.submitOrderLoading = false;
            }
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useOrderStore, import.meta.hot));
}
