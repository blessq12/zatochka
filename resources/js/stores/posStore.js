import axios from "axios";
import { defineStore } from "pinia";
import { toastService } from "../services/toastService.js";
import { orderService } from "../services/pos/OrderService.js";

export const usePosStore = defineStore("pos", {
    state: () => ({
        user: null,
        token: null,
        isLoading: false,
        error: null,
        ordersCount: {
            new: 0,
            in_work: 0,
        },
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
    },

    actions: {
        /**
         * Авторизация мастера
         */
        async login(credentials) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.post("/api/pos/login", credentials);

                if (response.data.token && response.data.user) {
                    this.token = response.data.token;
                    this.user = response.data.user;

                    localStorage.setItem("pos_token", this.token);
                    toastService.success("Добро пожаловать!");

                    // Загружаем счетчики заказов после логина
                    await this.getOrdersCount();

                    return { success: true, data: response.data };
                } else {
                    this.error = "Ошибка авторизации";
                    return { success: false, error: this.error };
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Ошибка авторизации";
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Получить пользователя по токену из localStorage
         */
        async getMe() {
            const token = localStorage.getItem("pos_token");
            if (!token) {
                this.token = null;
                this.user = null;
                return false;
            }

            this.token = token;
            this.isLoading = true;

            try {
                const response = await axios.get("/api/pos/me", {
                    headers: { Authorization: `Bearer ${token}` },
                });

                if (response.data.user) {
                    this.user = response.data.user;
                    // Загружаем счетчики заказов после получения пользователя
                    await this.getOrdersCount();
                    return true;
                } else {
                    this.logout();
                    return false;
                }
            } catch (error) {
                console.error("Auth check failed:", error);
                this.logout();
                return false;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Получить счетчики заказов
         */
        async getOrdersCount() {
            try {
                this.ordersCount = await orderService.getOrdersCount();
            } catch (error) {
                console.error("Failed to fetch orders count:", error);
            }
        },

        /**
         * Обновить профиль мастера
         */
        async updateProfile(profileData) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.post("/api/pos/profile/update", profileData);

                if (response.data.user) {
                    this.user = response.data.user;
                    toastService.success("Профиль успешно обновлён");
                    return { success: true, data: response.data };
                } else {
                    this.error = "Ошибка обновления профиля";
                    return { success: false, error: this.error };
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Ошибка обновления профиля";
                const errors = error.response?.data?.errors;
                return {
                    success: false,
                    error: this.error,
                    errors: errors || {},
                };
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Выход из системы
         */
        async logout() {
            try {
                if (this.token) {
                    await axios.post(
                        "/api/pos/logout",
                        {},
                        {
                            headers: { Authorization: `Bearer ${this.token}` },
                        }
                    );
                }
            } catch (error) {
                console.error("Logout error:", error);
            } finally {
                this.token = null;
                this.user = null;
                this.error = null;
                localStorage.removeItem("pos_token");
            }
        },
    },
});
