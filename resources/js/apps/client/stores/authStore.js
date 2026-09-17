import axios from "axios";
import { defineStore } from "pinia";
import createLoginRequestDto from "../dto/auth/loginRequestDto.js";
import createRegisterRequestDto from "../dto/auth/registerRequestDto.js";

const TOKEN_KEY = "auth_token";
const EXPECTED_ACTOR_TYPE = "clients";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        token: null,
        isLoading: false,
        requiresPasswordSet: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
        actorId: (state) => state.user?.actor?.id ?? null,
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
        },

        assertClientRole(actorType) {
            if (actorType !== EXPECTED_ACTOR_TYPE) {
                throw new Error("Нет доступа к кабинету клиента");
            }
        },

        async login(credentials) {
            this.isLoading = true;

            try {
                const payload = createLoginRequestDto({
                    email: credentials.email,
                    password: credentials.password,
                    expectedActorType: EXPECTED_ACTOR_TYPE,
                });
                const response = await axios.post("/api/identity/login", payload);

                this.assertClientRole(response.data.actor?.type);
                this.applySession(response.data);

                return { success: true, data: response.data };
            } catch (error) {
                await this.logout();
                const message =
                    error.response?.data?.message ||
                    error.message ||
                    "Ошибка авторизации";
                return { success: false, error: message };
            } finally {
                this.isLoading = false;
            }
        },

        async register(userData) {
            this.isLoading = true;

            try {
                const payload = createRegisterRequestDto({
                    email: userData.email,
                    password: userData.password,
                });
                const response = await axios.post(
                    "/api/identity/register",
                    payload
                );

                this.assertClientRole(response.data.actor?.type);
                this.applySession(response.data);

                return { success: true, data: response.data };
            } catch (error) {
                await this.logout();
                const message =
                    error.response?.data?.message || "Ошибка регистрации";
                return { success: false, error: message };
            } finally {
                this.isLoading = false;
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await axios.post("/api/identity/logout");
                }
            } catch {
                // ignore
            }

            this.token = null;
            this.user = null;
            this.requiresPasswordSet = false;
            localStorage.removeItem(TOKEN_KEY);
        },

        async fetchMe() {
            const response = await axios.get("/api/identity/me");
            this.assertClientRole(response.data.actor?.type);
            this.user = {
                id: response.data.id,
                email: response.data.email,
                actor: response.data.actor,
            };
            return this.user;
        },

        async checkAuth() {
            const token = localStorage.getItem(TOKEN_KEY);
            if (!token) {
                return false;
            }

            this.token = token;
            this.isLoading = true;

            try {
                await this.fetchMe();
                return true;
            } catch (error) {
                await this.logout();
                return false;
            } finally {
                this.isLoading = false;
            }
        },

        async updateClient() {
            return { success: false, error: "Обновление профиля пока недоступно" };
        },

        async setPassword() {
            return { success: false, error: "Смена пароля пока недоступна" };
        },
    },
});
