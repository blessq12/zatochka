import axios from "axios";
import { defineStore } from "pinia";

const TOKEN_KEY = "manager_token";
const USER_KEY = "manager_user";
const EXPECTED_ACTOR_TYPE = "managers";

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
        applySession(payload) {
            this.token = payload.token;
            this.user = {
                id: payload.id,
                email: payload.email,
                actor: payload.actor,
            };
            localStorage.setItem(TOKEN_KEY, this.token);
            persistUser(this.user);
        },

        assertManagerRole(actorType) {
            if (actorType !== EXPECTED_ACTOR_TYPE) {
                throw new Error("Нет доступа к панели менеджера");
            }
        },

        async login(credentials) {
            this.isLoading = true;
            this.error = null;
            try {
                const { data } = await axios.post("/api/identity/login", {
                    email: credentials.email,
                    password: credentials.password,
                    expected_actor_type: EXPECTED_ACTOR_TYPE,
                });

                this.assertManagerRole(data.actor?.type);
                this.applySession(data);
                return { success: true };
            } catch (error) {
                await this.clearSession();
                this.error =
                    error.response?.data?.message ||
                    error.message ||
                    "Ошибка авторизации";
                return { success: false, error: this.error };
            } finally {
                this.isLoading = false;
            }
        },

        async restoreSession() {
            const token = localStorage.getItem(TOKEN_KEY);
            const user = restoreUser();
            if (!token || !user) {
                await this.clearSession();
                return false;
            }

            this.token = token;
            this.user = user;

            try {
                const { data } = await axios.get("/api/identity/me");
                this.assertManagerRole(data.actor?.type);
                this.user = {
                    id: data.id,
                    email: data.email,
                    actor: data.actor,
                };
                persistUser(this.user);
                return true;
            } catch {
                await this.clearSession();
                return false;
            }
        },

        async clearSession() {
            this.token = null;
            this.user = null;
            this.error = null;
            localStorage.removeItem(TOKEN_KEY);
            persistUser(null);
        },

        async logout() {
            try {
                if (this.token) {
                    await axios.post("/api/identity/logout");
                }
            } catch {
                // ignore
            }
            await this.clearSession();
        },
    },
});
