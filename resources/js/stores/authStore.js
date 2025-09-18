import axios from "axios";
import { defineStore } from "pinia";
import createLoginRequestDto from "../dto/auth/loginRequestDto.js";
import createRegisterRequestDto from "../dto/auth/registerRequestDto.js";
import createUpdateClientRequestDto from "../dto/client/updateClientRequestDto.js";
import { toastService } from "../services/toastService.js";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        bonusAccount: null,
        token: null,
        isLoading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        userRoles: (state) => state.user?.roles || [],
    },

    actions: {
        async login(credentials) {
            this.isLoading = true;
            this.error = null;

            try {
                const payload = createLoginRequestDto(credentials);
                const response = await axios.post("/api/login", payload);

                this.token = response.data.token;
                this.user = response.data.client;

                localStorage.setItem("auth_token", this.token);
                toastService.success("Добро пожаловать!");

                return { success: true, data: response.data };
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Ошибка авторизации";
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },

        async register(userData) {
            this.isLoading = true;
            this.error = null;

            try {
                const payload = createRegisterRequestDto(userData);
                const response = await axios.post("/api/register", payload);

                this.token = response.data.token;
                this.user = response.data.client;

                localStorage.setItem("auth_token", this.token);
                toastService.success("Регистрация успешна!");

                return { success: true, data: response.data };
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Ошибка регистрации";
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },

        async logout() {
            this.token = null;
            this.user = null;
            this.error = null;
            localStorage.removeItem("auth_token");
        },

        async checkAuth() {
            const token = localStorage.getItem("auth_token");
            if (!token) return false;

            this.token = token;
            this.isLoading = true;

            try {
                const response = await axios.get("/api/client/self", {
                    headers: { Authorization: `Bearer ${token}` },
                });

                this.user = response.data.client;
                this.bonusAccount = response.data.bonusAccount;
                return true;
            } catch (error) {
                console.error("Auth check failed:", error);
                // this.logout();
                return false;
            } finally {
                this.isLoading = false;
            }
        },

        async updateClient(formData) {
            this.isLoading = true;
            this.error = null;

            try {
                const payload = createUpdateClientRequestDto({
                    id: this.user?.id,
                    ...formData,
                });

                const response = await axios.post(
                    "/api/client/update",
                    payload,
                    { headers: { Authorization: `Bearer ${this.token}` } }
                );

                this.user = response.data.client;
                toastService.success("Профиль обновлён");
                return { success: true, data: response.data };
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Ошибка обновления профиля";
                toastService.error(this.error);
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },

        clearError() {
            this.error = null;
        },
    },
});
