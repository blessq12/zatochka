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
        error: null,
        telegramVerified: false,
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
            try {
                if (this.token) {
                    await axios.post("/api/logout", {}, {
                        headers: { Authorization: `Bearer ${this.token}` },
                    });
                }
            } catch (error) {
                console.error("Logout error:", error);
            } finally {
                this.token = null;
                this.user = null;
                this.error = null;
                this.telegramVerified = false;
                localStorage.removeItem("auth_token");
            }
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

                // Инициализируем статус Telegram на основе данных пользователя
                this.telegramVerified =
                    this.user?.telegram_verified_at !== null &&
                    this.user?.telegram_verified_at !== undefined;

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

        setTelegramVerified(verified) {
            this.telegramVerified = verified;
        },

        async checkTelegramChat() {
            this.error = null;

            try {
                const response = await axios.post(
                    "/api/telegram/check-chat-is-exists",
                    {},
                    { headers: { Authorization: `Bearer ${this.token}` } }
                );

                // Обрабатываем новый формат ответа
                const chatExists = response.data.chat_exists || false;

                if (chatExists) {
                    toastService.success("Telegram чат найден");
                } else {
                    toastService.warning(
                        "Telegram чат не найден. Перейдите в бота и нажмите /start"
                    );
                }

                return {
                    success: true,
                    data: {
                        chatExists,
                        rawResponse: response.data,
                    },
                };
            } catch (error) {
                const errorMessage =
                    error.response?.data?.message ||
                    "Ошибка проверки Telegram чата";
                toastService.error(errorMessage);
                return { success: false, error: errorMessage };
            }
        },

        async sendTelegramVerificationCode() {
            // НЕ устанавливаем isLoading = true, чтобы не вызывать ререндер родителя
            this.error = null;

            try {
                const response = await axios.post(
                    "/api/telegram/send-verification-code",
                    {},
                    { headers: { Authorization: `Bearer ${this.token}` } }
                );

                // Парсим ответ сервера
                const {
                    success,
                    message,
                    telegram_username,
                    expires_in_minutes,
                } = response.data;

                if (success) {
                    toastService.success(
                        `Код отправлен в Telegram (@${telegram_username}). Действителен ${expires_in_minutes} мин.`
                    );
                } else {
                    toastService.error(message || "Ошибка отправки кода");
                }

                return {
                    success: success,
                    data: {
                        message,
                        telegramUsername: telegram_username,
                        expiresInMinutes: expires_in_minutes,
                    },
                };
            } catch (error) {
                const errorMessage =
                    error.response?.data?.message ||
                    "Ошибка отправки кода подтверждения";
                toastService.error(errorMessage);
                return { success: false, error: errorMessage };
            }
        },

        async verifyTelegramCode(code) {
            // НЕ устанавливаем isLoading = true, чтобы не вызывать ререндер родителя
            this.error = null;

            try {
                const response = await axios.post(
                    "/api/telegram/verify-code",
                    { code },
                    { headers: { Authorization: `Bearer ${this.token}` } }
                );

                // Парсим ответ сервера
                const {
                    success,
                    message,
                    telegram_username,
                    verified_at,
                    client,
                } = response.data;

                if (success) {
                    // Обновляем данные пользователя после успешной верификации
                    if (client) {
                        this.user = client;
                    }

                    // Устанавливаем статус подтверждения Telegram
                    this.telegramVerified = true;

                    const verifiedDate = new Date(verified_at).toLocaleString(
                        "ru-RU"
                    );
                    toastService.success(
                        `Telegram (@${telegram_username}) успешно подтвержден!`
                    );
                } else {
                    toastService.error(message || "Ошибка подтверждения кода");
                }

                return {
                    success: success,
                    data: {
                        message,
                        telegramUsername: telegram_username,
                        verifiedAt: verified_at,
                        client: client,
                    },
                };
            } catch (error) {
                const errorMessage =
                    error.response?.data?.message ||
                    "Ошибка подтверждения кода";
                toastService.error(errorMessage);
                return { success: false, error: errorMessage };
            }
        },
    },
});
