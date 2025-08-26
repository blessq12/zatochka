import { defineStore } from "pinia";
import { useAuthStore } from "./auth";

export const useBonusStore = defineStore("bonus", {
    state: () => ({
        balance: null,
        transactions: [],
        pagination: null,
        loading: false,
        error: null,
    }),

    actions: {
        getHeaders() {
            const auth = useAuthStore();
            return auth.getHeaders();
        },

        async fetchBalance() {
            this.loading = true;
            this.error = null;
            try {
                const response = await fetch("/api/client/bonus/balance", {
                    headers: this.getHeaders(),
                });
                const data = await response.json();
                if (!response.ok)
                    throw new Error(data.message || "Ошибка получения баланса");
                this.balance = data.data;
                return this.balance;
            } catch (e) {
                this.error = e.message;
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async fetchTransactions(page = 1, perPage = 10) {
            this.loading = true;
            this.error = null;
            try {
                const url = `/api/client/bonus/transactions?per_page=${perPage}&page=${page}`;
                const response = await fetch(url, {
                    headers: this.getHeaders(),
                });
                const data = await response.json();
                if (!response.ok)
                    throw new Error(
                        data.message || "Ошибка получения операций"
                    );

                const pageItems = data.data.data || [];
                this.transactions =
                    page === 1
                        ? pageItems
                        : [...this.transactions, ...pageItems];
                this.pagination = {
                    current_page: data.data.current_page,
                    per_page: data.data.per_page,
                    total: data.data.total,
                };

                return { items: pageItems, pagination: this.pagination };
            } catch (e) {
                this.error = e.message;
                throw e;
            } finally {
                this.loading = false;
            }
        },

        reset() {
            this.balance = null;
            this.transactions = [];
            this.pagination = null;
            this.loading = false;
            this.error = null;
        },
    },
});
