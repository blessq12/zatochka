import axios from "axios";
import { acceptHMRUpdate, defineStore } from "pinia";

export const useReviewsStore = defineStore("reviews", {
    state: () => ({
        items: [],
        averageRating: null,
        isLoading: false,
        error: null,
        isLoaded: false,
    }),

    actions: {
        async fetchPublished(limit = 20) {
            if (this.isLoaded) {
                return { success: true, items: this.items };
            }

            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/reviews", {
                    params: { limit },
                });
                const data = response.data.data || {};
                this.items = Array.isArray(data.items) ? data.items : [];
                this.averageRating = data.average_rating ?? null;
                this.isLoaded = true;
                return { success: true, items: this.items };
            } catch (error) {
                const message =
                    error.response?.data?.message ||
                    "Ошибка загрузки отзывов";
                this.error = message;
                return { success: false, error: message };
            } finally {
                this.isLoading = false;
            }
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useReviewsStore, import.meta.hot));
}
