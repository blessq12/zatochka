import { acceptHMRUpdate, defineStore } from "pinia";

export const useOrderStore = defineStore("order", {
    state: () => ({
        orders: [],
        currentOrder: null,
    }),
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useOrderStore, import.meta.hot));
}
