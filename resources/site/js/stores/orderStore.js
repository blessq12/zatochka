import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";
import createPublicOrderRequestDto from "../dto/form/publicOrderRequestDto.js";

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

                const response = await axios.post("/api/public/order-drafts", payload);

                return { success: true, data: response.data };
            } catch (error) {
                const data = error.response?.data;
                let message = data?.message || "Ошибка создания заявки";
                if (data?.errors && typeof data.errors === "object") {
                    const first = Object.values(data.errors).flat()[0];
                    if (first) {
                        message = String(first);
                    }
                }
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
