import axios from "axios";
import { defineStore } from "pinia";
import createLoginRequestDto from "../dto/auth/loginRequestDto.js";
import createRegisterRequestDto from "../dto/auth/registerRequestDto.js";
import createUpdateClientRequestDto from "../dto/client/updateClientRequestDto.js";

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
                full_name: null,
                phone: null,
                birth_date: null,
                delivery_address: null,
            };
            localStorage.setItem(TOKEN_KEY, this.token);
        },

        mergeActorProfile(actor) {
            if (!this.user) {
                return;
            }

            this.user = {
                ...this.user,
                email: actor.email || this.user.email,
                actor: {
                    type: actor.type || this.user.actor?.type,
                    id: actor.id ?? this.user.actor?.id,
                },
                full_name: actor.name ?? null,
                phone: actor.phone ?? null,
                birth_date: actor.birthday ?? null,
                delivery_address: actor.delivery_address ?? null,
            };
        },

        assertClientRole(actorType) {
            if (actorType !== EXPECTED_ACTOR_TYPE) {
                throw new Error("Нет доступа к кабинету клиента");
            }
        },

        async fetchActorProfile() {
            const actorId = this.actorId;
            if (!actorId) {
                return null;
            }

            const { data } = await axios.get(`/api/actors/clients/${actorId}`);
            this.mergeActorProfile(data);
            return data;
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
                await this.fetchActorProfile();

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
                await this.fetchActorProfile();

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
                full_name: this.user?.full_name ?? null,
                phone: this.user?.phone ?? null,
                birth_date: this.user?.birth_date ?? null,
                delivery_address: this.user?.delivery_address ?? null,
            };
            await this.fetchActorProfile();
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

        async updateClient(input) {
            const actorId = this.actorId;
            if (!actorId) {
                return { success: false, error: "Профиль не найден" };
            }

            try {
                const dto = createUpdateClientRequestDto(input);
                const payload = {};

                if (Object.prototype.hasOwnProperty.call(dto, "full_name")) {
                    payload.name = dto.full_name || null;
                }
                if (Object.prototype.hasOwnProperty.call(dto, "phone")) {
                    payload.phone = dto.phone || null;
                }
                if (Object.prototype.hasOwnProperty.call(dto, "birth_date")) {
                    payload.birthday = dto.birth_date || null;
                }
                if (Object.prototype.hasOwnProperty.call(dto, "delivery_address")) {
                    payload.delivery_address = dto.delivery_address || null;
                }

                const { data } = await axios.patch(
                    `/api/actors/clients/${actorId}`,
                    payload
                );
                this.mergeActorProfile(data);
                return { success: true, data };
            } catch (error) {
                const message =
                    error.response?.data?.message ||
                    (error.response?.data?.errors
                        ? Object.values(error.response.data.errors).flat().join(" ")
                        : "Ошибка обновления профиля");
                return { success: false, error: message };
            }
        },

        async setPassword() {
            return { success: false, error: "Смена пароля пока недоступна" };
        },
    },
});
