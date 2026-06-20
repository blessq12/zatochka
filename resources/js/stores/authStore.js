import axios from "axios";
import { defineStore } from "pinia";
import createLoginRequestDto from "../dto/auth/loginRequestDto.js";
import createRegisterRequestDto from "../dto/auth/registerRequestDto.js";
import createUpdateClientRequestDto from "../dto/client/updateClientRequestDto.js";
import { toastService } from "../services/toastService.js";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        token: null,
        isLoading: false,
        /** true — вошёл по временному паролю, нужно показать модалку установки постоянного */
        requiresPasswordSet: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        async login(credentials) {
            this.isLoading = true;

            try {
                const payload = createLoginRequestDto(credentials);
                const response = await axios.post("/api/auth/login", payload);

                this.token = response.data.token;
                localStorage.setItem("auth_token", this.token);

                await this.fetchProfile();
                toastService.success("Добро пожаловать!");

                return { success: true, data: response.data };
            } catch (error) {
                const message =
                    error.response?.data?.message || "Ошибка авторизации";
                return { success: false, error: message };
            } finally {
                this.isLoading = false;
            }
        },

        async register(userData) {
            this.isLoading = true;

            try {
                const payload = createRegisterRequestDto(userData);
                const response = await axios.post(
                    "/api/auth/register",
                    payload
                );

                this.token = response.data.token;
                localStorage.setItem("auth_token", this.token);

                await this.fetchProfile();
                toastService.success("Регистрация успешна!");

                return { success: true, data: response.data };
            } catch (error) {
                const message =
                    error.response?.data?.message || "Ошибка регистрации";
                return { success: false, error: message };
            } finally {
                this.isLoading = false;
            }
        },

        async logout() {
            this.token = null;
            this.user = null;
            this.requiresPasswordSet = false;
            localStorage.removeItem("auth_token");
        },

        async fetchProfile() {
            const response = await axios.get("/api/client/profile");
            this.user = response.data.data;
            this.requiresPasswordSet =
                this.user?.requires_password_set === true;

            return this.user;
        },

        async checkAuth() {
            const token = localStorage.getItem("auth_token");
            if (!token) {
                return false;
            }

            this.token = token;
            this.isLoading = true;

            try {
                await this.fetchProfile();
                return true;
            } catch (error) {
                if (error.response?.status === 401) {
                    await this.logout();
                }
                console.error("Auth check failed:", error);
                return false;
            } finally {
                this.isLoading = false;
            }
        },

        async updateClient(formData) {
            this.isLoading = true;

            try {
                const payload = createUpdateClientRequestDto(formData);

                const response = await axios.patch(
                    "/api/client/profile",
                    payload,
                    { headers: { Authorization: `Bearer ${this.token}` } }
                );

                this.user = response.data.data;
                toastService.success("Профиль обновлён");
                return { success: true, data: response.data };
            } catch (error) {
                const message =
                    error.response?.data?.message ||
                    "Ошибка обновления профиля";
                toastService.error(message);
                return { success: false, error: message };
            } finally {
                this.isLoading = false;
            }
        },

        async setPassword(newPassword, newPasswordConfirmation) {
            this.isLoading = true;

            try {
                const response = await axios.post(
                    "/api/client/password",
                    {
                        password: newPassword,
                        password_confirmation: newPasswordConfirmation,
                    },
                    { headers: { Authorization: `Bearer ${this.token}` } }
                );

                this.requiresPasswordSet = false;
                this.user = response.data.data;
                toastService.success("Пароль успешно установлен");
                return { success: true, data: response.data };
            } catch (error) {
                const message =
                    error.response?.data?.message ||
                    "Ошибка установки пароля";
                toastService.error(message);
                return { success: false, error: message };
            } finally {
                this.isLoading = false;
            }
        },
    },
});
