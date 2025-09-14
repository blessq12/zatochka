import { acceptHMRUpdate, defineStore } from "pinia";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        auth: false,
    }),
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useAuthStore, import.meta.hot));
}
