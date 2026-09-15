import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

function toTelHref(phone) {
    if (!phone) {
        return "";
    }

    const digits = String(phone).replace(/\D+/g, "");

    if (!digits) {
        return "";
    }

    return digits.startsWith("8") && digits.length === 11
        ? `+7${digits.slice(1)}`
        : digits.startsWith("7")
          ? `+${digits}`
          : `+${digits}`;
}

export const useBootstrapStore = defineStore("bootstrap", {
    state: () => ({
        data: null,
        isLoading: false,
        error: null,
    }),

    getters: {
        isLoaded: (state) => state.data !== null,

        prices: (state) => state.data?.prices ?? [],

        sharpeningPrices(state) {
            return (state.data?.prices || []).filter(
                (item) => item.category === "sharpening"
            );
        },

        repairPrices(state) {
            return (state.data?.prices || []).filter(
                (item) => item.category === "repair"
            );
        },

        contacts: (state) => state.data?.contacts ?? {},

        phone: (state) => state.data?.contacts?.phone ?? "",

        phoneTel() {
            return toTelHref(this.phone);
        },

        socialLinks: (state) => state.data?.contacts?.social?.links ?? [],

        scheduleDays: (state) => state.data?.schedule?.days ?? [],

        deliveryInfo: (state) => state.data?.delivery_info ?? {},

        freeDeliveryConditions: (state) =>
            state.data?.delivery_info?.free_conditions ?? [],

        deliveryAdvantages: (state) =>
            state.data?.delivery_info?.advantages ?? [],

        company: (state) => state.data?.company ?? {},

        faqItems: (state) => state.data?.faq?.items ?? [],
    },

    actions: {
        async fetchBootstrap() {
            if (this.data) {
                return { success: true, data: this.data };
            }

            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/bootstrap");
                this.data = response.data.data;
                return { success: true, data: this.data };
            } catch (error) {
                const message =
                    error.response?.data?.message ||
                    "Ошибка загрузки данных сайта";
                this.error = message;
                return { success: false, error: message };
            } finally {
                this.isLoading = false;
            }
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useBootstrapStore, import.meta.hot));
}
