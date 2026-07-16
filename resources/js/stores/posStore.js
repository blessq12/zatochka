import axios from "axios";
import { defineStore } from "pinia";
import { toastService } from "../services/toastService.js";
import { orderService } from "../services/pos/OrderService.js";

const POS_TOKEN_KEY = "pos_token";
const POS_USER_KEY = "pos_user";

const persistUser = (user) => {
    if (user) {
        localStorage.setItem(POS_USER_KEY, JSON.stringify(user));
    } else {
        localStorage.removeItem(POS_USER_KEY);
    }
};

const restoreUser = () => {
    const raw = localStorage.getItem(POS_USER_KEY);
    if (!raw) {
        return null;
    }

    try {
        return JSON.parse(raw);
    } catch {
        return null;
    }
};

export const usePosStore = defineStore("pos", {
    state: () => ({
        user: null,
        token: null,
        isLoading: false,
        error: null,
        ordersCount: {
            new: 0,
            in_work: 0,
            waiting_parts: 0,
            ready: 0,
        },
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
    },

    actions: {
        async login(credentials) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.post("/api/v1/auth/login", credentials);
                const { token, master } = response.data;

                if (!token || !master) {
                    this.error = "Ошибка авторизации";
                    return { success: false, error: this.error };
                }

                this.token = token;
                this.user = master;
                localStorage.setItem(POS_TOKEN_KEY, token);
                persistUser(master);
                toastService.success("Добро пожаловать!");

                await this.getOrdersCount();

                return { success: true, data: response.data };
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Ошибка авторизации";
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },

        restoreSession() {
            const token = localStorage.getItem(POS_TOKEN_KEY);
            const user = restoreUser();

            if (!token || !user) {
                this.logout();
                return false;
            }

            this.token = token;
            this.user = user;
            this.getOrdersCount();

            return true;
        },

        async getOrdersCount() {
            try {
                const counts = await orderService.getOrdersCount();
                this.ordersCount.new = counts.new || 0;
                this.ordersCount.in_work = counts.in_work || 0;
                this.ordersCount.waiting_parts = counts.waiting_parts || 0;
                this.ordersCount.ready = counts.ready || 0;
            } catch (error) {
                console.error("Failed to fetch orders count:", error);
                this.ordersCount.new = 0;
                this.ordersCount.in_work = 0;
                this.ordersCount.waiting_parts = 0;
                this.ordersCount.ready = 0;
            }
        },

        logout() {
            this.token = null;
            this.user = null;
            this.error = null;
            this.ordersCount = {
                new: 0,
                in_work: 0,
                waiting_parts: 0,
                ready: 0,
            };
            localStorage.removeItem(POS_TOKEN_KEY);
            persistUser(null);
        },
    },
});
