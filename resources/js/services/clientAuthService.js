class ClientAuthService {
    constructor() {
        this.baseUrl = "/api/client";
        this.token = localStorage.getItem("client_token");
    }

    // Установка токена
    setToken(token) {
        this.token = token;
        localStorage.setItem("client_token", token);
    }

    // Получение токена
    getToken() {
        return this.token;
    }

    // Удаление токена
    removeToken() {
        this.token = null;
        localStorage.removeItem("client_token");
    }

    // Проверка авторизации
    isAuthenticated() {
        return !!this.token;
    }

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
    }

    // Регистрация
    async register(userData) {
        try {
            const response = await fetch(`${this.baseUrl}/register`, {
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

            return data;
        } catch (error) {
            throw error;
        }
    }

    // Вход
    async login(credentials) {
        try {
            const response = await fetch(`${this.baseUrl}/login`, {
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

            return data;
        } catch (error) {
            throw error;
        }
    }

    // Выход
    async logout() {
        try {
            const response = await fetch(`${this.baseUrl}/logout`, {
                method: "POST",
                headers: this.getHeaders(),
            });

            this.removeToken();
            return response.json();
        } catch (error) {
            this.removeToken();
            throw error;
        }
    }

    // Получение профиля
    async getProfile() {
        try {
            const response = await fetch(`${this.baseUrl}/profile`, {
                method: "GET",
                headers: this.getHeaders(),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Ошибка получения профиля");
            }

            return data;
        } catch (error) {
            throw error;
        }
    }

    // Обновление профиля
    async updateProfile(profileData) {
        try {
            const response = await fetch(`${this.baseUrl}/profile`, {
                method: "PUT",
                headers: this.getHeaders(),
                body: JSON.stringify(profileData),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Ошибка обновления профиля");
            }

            return data;
        } catch (error) {
            throw error;
        }
    }

    // Изменение пароля
    async changePassword(passwordData) {
        try {
            const response = await fetch(`${this.baseUrl}/change-password`, {
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
            throw error;
        }
    }

    // Проверка токена
    async checkToken() {
        try {
            const response = await fetch(`${this.baseUrl}/check-token`, {
                method: "GET",
                headers: this.getHeaders(),
            });

            const data = await response.json();

            if (!response.ok) {
                this.removeToken();
                throw new Error(data.message || "Токен недействителен");
            }

            return data;
        } catch (error) {
            this.removeToken();
            throw error;
        }
    }

    // Сброс пароля - отправка ссылки
    async forgotPassword(phone) {
        try {
            const response = await fetch(`${this.baseUrl}/forgot-password`, {
                method: "POST",
                headers: this.getHeaders(),
                body: JSON.stringify({ phone }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message || "Ошибка отправки ссылки для сброса"
                );
            }

            return data;
        } catch (error) {
            throw error;
        }
    }

    // Сброс пароля - установка нового
    async resetPassword(resetData) {
        try {
            const response = await fetch(`${this.baseUrl}/reset-password`, {
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
            throw error;
        }
    }

    // Telegram верификация - отправка кода
    async sendTelegramCode(phone) {
        try {
            const response = await fetch(`${this.baseUrl}/telegram/send-code`, {
                method: "POST",
                headers: this.getHeaders(),
                body: JSON.stringify({ phone }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Ошибка отправки кода");
            }

            return data;
        } catch (error) {
            throw error;
        }
    }

    // Telegram верификация - проверка кода
    async verifyTelegramCode(phone, code) {
        try {
            const response = await fetch(
                `${this.baseUrl}/telegram/verify-code`,
                {
                    method: "POST",
                    headers: this.getHeaders(),
                    body: JSON.stringify({ phone, code }),
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
    }

    // Telegram верификация - статус
    async getTelegramStatus(phone) {
        try {
            const response = await fetch(
                `${this.baseUrl}/telegram/status?phone=${phone}`,
                {
                    method: "GET",
                    headers: this.getHeaders(),
                }
            );

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Ошибка получения статуса");
            }

            return data;
        } catch (error) {
            throw error;
        }
    }

    // Telegram верификация - обновление аккаунта
    async updateTelegram(phone, telegram) {
        try {
            const response = await fetch(`${this.baseUrl}/telegram/update`, {
                method: "PUT",
                headers: this.getHeaders(),
                body: JSON.stringify({ phone, telegram }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Ошибка обновления Telegram");
            }

            return data;
        } catch (error) {
            throw error;
        }
    }
}

// Создаем экземпляр сервиса
const clientAuthService = new ClientAuthService();

export default clientAuthService;
