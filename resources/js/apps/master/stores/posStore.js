import axios from "axios";
import { defineStore } from "pinia";

const POS_TOKEN_KEY = "pos_token";
const POS_USER_KEY = "pos_user";
const EXPECTED_ACTOR_TYPE = "masters";

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
            localStorage.setItem(POS_TOKEN_KEY, this.token);
            persistUser(this.user);
        },

        assertMasterRole(actorType) {
            if (actorType !== EXPECTED_ACTOR_TYPE) {
                throw new Error("Нет доступа к кассе мастера");
            }
        },

        async login(credentials) {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await axios.post("/api/identity/login", {
                    email: credentials.email,
                    password: credentials.password,
                    expected_actor_type: EXPECTED_ACTOR_TYPE,
                });

                this.assertMasterRole(response.data.actor?.type);
                this.applySession(response.data);

                return { success: true, data: response.data };
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
            const token = localStorage.getItem(POS_TOKEN_KEY);
            const user = restoreUser();

            if (!token || !user) {
                await this.clearSession();
                return false;
            }

            this.token = token;
            this.user = user;

            try {
                const { data } = await axios.get("/api/identity/me");
                this.assertMasterRole(data.actor?.type);
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
            localStorage.removeItem(POS_TOKEN_KEY);
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
