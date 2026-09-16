import axios from "axios";
import { defineStore } from "pinia";

const TOKEN_KEY = "manager_token";
const USER_KEY = "manager_user";

const persistUser = (user) => {
    if (user) {
        localStorage.setItem(USER_KEY, JSON.stringify(user));
    } else {
        localStorage.removeItem(USER_KEY);
    }
};

const restoreUser = () => {
    const raw = localStorage.getItem(USER_KEY);
    if (!raw) return null;
    try {
        return JSON.parse(raw);
    } catch {
        return null;
    }
};

export const useManagerStore = defineStore("manager", {
    state: () => ({
        user: null,
        token: null,
        isLoading: false,
        error: null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
    },
    actions: {
        async login(credentials) {
            this.isLoading = true;
            this.error = null;
            try {
                const { data } = await axios.post("/api/v1/auth/manager/login", credentials);
                if (!data.token || !data.manager) {
                    this.error = "Ошибка авторизации";
                    return { success: false, error: this.error };
                }
                this.token = data.token;
                this.user = data.manager;
                localStorage.setItem(TOKEN_KEY, data.token);
                persistUser(data.manager);
                return { success: true };
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    error.response?.data?.errors?.email?.[0] ||
                    "Ошибка авторизации";
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },
        restoreSession() {
            const token = localStorage.getItem(TOKEN_KEY);
            const user = restoreUser();
            if (!token || !user) {
                this.logout();
                return false;
            }
            this.token = token;
            this.user = user;
            return true;
        },
        async logout() {
            try {
                if (this.token) {
                    await axios.post("/api/v1/auth/logout");
                }
            } catch {
                // ignore
            }
            this.token = null;
            this.user = null;
            this.error = null;
            localStorage.removeItem(TOKEN_KEY);
            persistUser(null);
        },
    },
});
