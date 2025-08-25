import { defineStore } from "pinia";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        token: localStorage.getItem("client_token") || null,
        user: null,
        isLoading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        getUser: (state) => state.user,
        getError: (state) => state.error,
        getLoading: (state) => state.isLoading,
        isTelegramVerified: (state) => {
            if (!state.user || !state.user.telegram_verified_at) {
                return false;
            }
            return !!state.user.telegram_verified_at;
        },
    },

    actions: {
        // Установка токена
        setToken(token) {
            this.token = token;
            localStorage.setItem("client_token", token);
        },

        // Удаление токена
        removeToken() {
            this.token = null;
            this.user = null;
            localStorage.removeItem("client_token");
        },

        // Заголовки для запросов
        getHeaders() {
            const headers = {
                "Content-Type": "application/json",
                Accept: "application/json",
            };

            if (this.token) {
                headers["Authorization"] = `Bearer ${this.token}`;
            }

            return headers;
        },

        async register(userData) {
            this.isLoading = true;
            this.error = null;

            // Показываем тост загрузки
            const loadingToast = window.toastService?.info(
                "Регистрируемся...",
                {
                    timeout: false,
                    closeButton: false,
                }
            );

            try {
                const response = await fetch("/api/client/register", {
                    method: "POST",
                    headers: this.getHeaders(),
                    body: JSON.stringify(userData),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Ошибка регистрации");
                }

                if (data.data?.token) {
                    this.setToken(data.data.token);
                }

                if (data.data?.user) {
                    console.log(
                        "👤 Setting user from register response:",
                        data.data.user
                    );
                    this.user = data.data.user;
                } else if (data.data?.client) {
                    console.log(
                        "👤 Setting user from client field (register):",
                        data.data.client
                    );
                    this.user = data.data.client;
                } else {
                    console.log(
                        "👤 No user in register response, fetching profile..."
                    );
                    try {
                        const profileResponse = await this.getProfile();
                        console.log(
                            "👤 Profile fetched after register:",
                            profileResponse
                        );
                    } catch (profileError) {
                        console.error(
                            "❌ Failed to fetch profile after register:",
                            profileError
                        );
                    }
                }

                // Закрываем loading тост и показываем успех
                if (window.toastService && loadingToast) {
                    window.toastService.dismiss(loadingToast);
                    window.toastService.success(
                        "Аккаунт создан успешно! Добро пожаловать!"
                    );
                } else if (window.toastService) {
                    window.toastService.success(
                        "Аккаунт создан успешно! Добро пожаловать!"
                    );
                }

                return data;
            } catch (error) {
                this.error = error.message;

                if (window.toastService && loadingToast) {
                    window.toastService.dismiss(loadingToast);
                    window.toastService.error(error.message);
                } else if (window.toastService) {
                    window.toastService.error(error.message);
                }

                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Вход
        async login(credentials) {
            this.isLoading = true;
            this.error = null;

            // Показываем тост загрузки
            const loadingToast = window.toastService?.info(
                "Входим в систему...",
                {
                    timeout: false,
                    closeButton: false,
                }
            );

            try {
                const response = await fetch("/api/client/login", {
                    method: "POST",
                    headers: this.getHeaders(),
                    body: JSON.stringify(credentials),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Ошибка входа");
                }

                if (data.data?.token) {
                    this.setToken(data.data.token);
                }

                if (data.data?.user) {
                    this.user = data.data.user;
                } else if (data.data?.client) {
                    this.user = data.data.client;
                } else {
                    // Если пользователь не пришел в ответе, получаем его отдельно
                    try {
                        await this.getProfile();
                    } catch (profileError) {
                        console.error("Failed to fetch profile:", profileError);
                    }
                }

                // Закрываем loading тост и показываем успех
                if (window.toastService && loadingToast) {
                    window.toastService.dismiss(loadingToast);
                    window.toastService.success("Вход выполнен успешно!");
                } else if (window.toastService) {
                    window.toastService.success("Вход выполнен успешно!");
                }

                return data;
            } catch (error) {
                console.error("❌ Login error:", error);
                this.error = error.message;

                // Закрываем loading тост и показываем ошибку
                if (window.toastService && loadingToast) {
                    window.toastService.dismiss(loadingToast);
                    window.toastService.error(error.message);
                } else if (window.toastService) {
                    window.toastService.error(error.message);
                }

                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Выход
        async logout() {
            this.isLoading = true;

            // Показываем тост загрузки
            const loadingToast = window.toastService?.info(
                "Выходим из системы...",
                {
                    timeout: false,
                    closeButton: false,
                }
            );

            try {
                const response = await fetch("/api/client/logout", {
                    method: "POST",
                    headers: this.getHeaders(),
                });

                this.removeToken();

                // Закрываем loading тост и показываем успех
                if (window.toastService && loadingToast) {
                    window.toastService.dismiss(loadingToast);
                    window.toastService.success("Выход выполнен успешно!");
                } else if (window.toastService) {
                    window.toastService.success("Выход выполнен успешно!");
                }

                return response.json();
            } catch (error) {
                this.removeToken();

                // Закрываем loading тост и показываем предупреждение
                if (window.toastService && loadingToast) {
                    window.toastService.dismiss(loadingToast);
                    window.toastService.warning(
                        "Выход выполнен (возможны проблемы с сервером)"
                    );
                } else if (window.toastService) {
                    window.toastService.warning(
                        "Выход выполнен (возможны проблемы с сервером)"
                    );
                }

                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Получение профиля
        async getProfile() {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await fetch("/api/client/profile", {
                    method: "GET",
                    headers: this.getHeaders(),
                });

                const data = await response.json();
                console.log("🔍 getProfile response:", data);

                if (!response.ok) {
                    throw new Error(data.message || "Ошибка получения профиля");
                }

                if (data.data?.user) {
                    console.log(
                        "👤 Setting user from user field (profile):",
                        data.data.user
                    );
                    this.user = data.data.user;
                } else if (data.data?.client) {
                    console.log(
                        "👤 Setting user from client field (profile):",
                        data.data.client
                    );
                    this.user = data.data.client;
                } else if (data.data) {
                    console.log(
                        "👤 Setting user from data field (profile):",
                        data.data
                    );
                    this.user = data.data;
                }

                return data;
            } catch (error) {
                this.error = error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Обновление профиля
        async updateProfile(profileData) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await fetch("/api/client/profile", {
                    method: "PUT",
                    headers: this.getHeaders(),
                    body: JSON.stringify(profileData),
                });

                const data = await response.json();
                console.log("🔍 updateProfile response:", data);

                if (!response.ok) {
                    throw new Error(
                        data.message || "Ошибка обновления профиля"
                    );
                }

                if (data.data?.user) {
                    console.log(
                        "👤 Setting user from user field (update):",
                        data.data.user
                    );
                    this.user = data.data.user;
                } else if (data.data?.client) {
                    console.log(
                        "👤 Setting user from client field (update):",
                        data.data.client
                    );
                    this.user = data.data.client;
                }

                return data;
            } catch (error) {
                this.error = error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Изменение пароля
        async changePassword(passwordData) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await fetch("/api/client/change-password", {
                    method: "PUT",
                    headers: this.getHeaders(),
                    body: JSON.stringify(passwordData),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Ошибка изменения пароля");
                }

                // При изменении пароля удаляем токен
                this.removeToken();

                return data;
            } catch (error) {
                this.error = error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Проверка токена
        async checkToken() {
            console.log("🔍 checkToken method called");
            this.isLoading = true;
            this.error = null;

            try {
                console.log("🔍 Making request to /api/client/check-token");
                const response = await fetch("/api/client/check-token", {
                    method: "GET",
                    headers: this.getHeaders(),
                });

                console.log("🔍 Response status:", response.status);
                const data = await response.json();
                console.log("🔍 checkToken response:", data);

                if (!response.ok) {
                    console.log("❌ Response not ok, removing token");
                    this.removeToken();
                    throw new Error(data.message || "Токен недействителен");
                }

                if (data.data?.user) {
                    console.log(
                        "👤 Setting user from user field:",
                        data.data.user
                    );
                    this.user = data.data.user;
                } else if (data.data?.client) {
                    console.log(
                        "👤 Setting user from client field:",
                        data.data.client
                    );
                    this.user = data.data.client;
                } else {
                    console.log("❌ No user or client data found in response");
                }

                console.log("🔍 checkToken completed, user state:", this.user);
                return data;
            } catch (error) {
                this.removeToken();
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Сброс пароля - отправка ссылки
        async forgotPassword(data) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await fetch("/api/client/forgot-password", {
                    method: "POST",
                    headers: this.getHeaders(),
                    body: JSON.stringify(data),
                });

                const responseData = await response.json();

                if (!response.ok) {
                    throw new Error(
                        responseData.message ||
                            "Ошибка отправки ссылки для сброса"
                    );
                }

                return responseData;
            } catch (error) {
                this.error = error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Сброс пароля - установка нового
        async resetPassword(resetData) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await fetch("/api/client/reset-password", {
                    method: "POST",
                    headers: this.getHeaders(),
                    body: JSON.stringify(resetData),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Ошибка сброса пароля");
                }

                return data;
            } catch (error) {
                this.error = error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Telegram верификация - статус
        async checkVerificationStatus() {
            try {
                const response = await fetch("/api/client/telegram/status", {
                    method: "GET",
                    headers: this.getHeaders(),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Ошибка получения статуса");
                }

                return data;
            } catch (error) {
                throw error;
            }
        },

        // Telegram верификация - отправка кода
        async sendVerificationCode() {
            try {
                const response = await fetch("/api/client/telegram/send-code", {
                    method: "POST",
                    headers: this.getHeaders(),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Ошибка отправки кода");
                }

                return data;
            } catch (error) {
                throw error;
            }
        },

        // Telegram верификация - проверка кода
        async verifyCode(code) {
            try {
                const response = await fetch(
                    "/api/client/telegram/verify-code",
                    {
                        method: "POST",
                        headers: this.getHeaders(),
                        body: JSON.stringify({ code }),
                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Ошибка верификации");
                }

                return data;
            } catch (error) {
                throw error;
            }
        },

        // Обновление Telegram аккаунта
        async updateTelegram(telegram) {
            try {
                const response = await fetch("/api/client/telegram/update", {
                    method: "PUT",
                    headers: this.getHeaders(),
                    body: JSON.stringify({ telegram }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(
                        data.message || "Ошибка обновления Telegram"
                    );
                }

                return data;
            } catch (error) {
                throw error;
            }
        },
    },
});
