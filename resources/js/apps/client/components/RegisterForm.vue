<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";

const fieldClass =
    "w-full max-w-full min-w-0 border border-white/20 bg-white/60 px-4 py-3.5 text-dark-gray-500 outline-none backdrop-blur-md transition-all duration-300 focus:border-[#C20A6C]/50 focus:ring-2 focus:ring-[#C20A6C]/30 dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200";

export default {
    name: "ClientRegisterForm",
    data() {
        return {
            form: {
                email: "",
                password: "",
                password_confirmation: "",
            },
            error: "",
            fieldClass,
        };
    },
    computed: {
        ...mapStores(useAuthStore),
    },
    methods: {
        async submit() {
            this.error = "";
            if (this.form.password !== this.form.password_confirmation) {
                this.error = "Пароли не совпадают";
                return;
            }
            const result = await this.authStore.register(this.form);
            if (result.success) {
                this.$router.replace({ name: "client.orders" });
                return;
            }
            this.error = result.error || "Ошибка регистрации";
        },
    },
};
</script>

<template>
    <div>
        <div class="mb-4 flex gap-1 bg-white/60 p-1 dark:bg-gray-800/60">
            <router-link
                :to="{ name: 'client.login' }"
                class="flex-1 px-3 py-3 text-center font-jost-bold text-base uppercase text-dark-gray-500 dark:text-gray-200"
            >
                Вход
            </router-link>
            <router-link
                :to="{ name: 'client.register' }"
                class="flex-1 bg-[#C20A6C] px-3 py-3 text-center font-jost-bold text-base uppercase text-white"
            >
                Регистрация
            </router-link>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div
                v-if="error"
                class="border border-red-300/50 bg-red-50/80 px-4 py-3 text-red-700 dark:border-red-600/50 dark:bg-red-900/30 dark:text-red-400"
            >
                {{ error }}
            </div>

            <label class="block">
                <span
                    class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200"
                >
                    Email
                </span>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    :class="fieldClass"
                />
            </label>

            <label class="block">
                <span
                    class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200"
                >
                    Пароль
                </span>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    :class="fieldClass"
                />
            </label>

            <label class="block">
                <span
                    class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200"
                >
                    Повтор пароля
                </span>
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    :class="fieldClass"
                />
            </label>

            <button
                type="submit"
                class="w-full bg-[#C20A6C] px-6 py-3.5 font-jost-bold text-base uppercase text-white disabled:opacity-50"
                :disabled="authStore.isLoading"
            >
                {{
                    authStore.isLoading
                        ? "Регистрация…"
                        : "Зарегистрироваться"
                }}
            </button>
        </form>
    </div>
</template>
