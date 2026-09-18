<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";

const fieldClass =
    "w-full max-w-full min-w-0 border border-white/20 bg-white/60 px-4 py-3.5 text-dark-gray-500 outline-none backdrop-blur-md transition-all duration-300 focus:border-[#C20A6C]/50 focus:ring-2 focus:ring-[#C20A6C]/30 dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200";

export default {
    name: "ClientLoginForm",
    data() {
        return {
            form: { email: "", password: "" },
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
            const result = await this.authStore.login(this.form);
            if (result.success) {
                const redirect = this.$route.query.redirect;
                this.$router.replace(
                    typeof redirect === "string" && redirect.startsWith("/")
                        ? redirect
                        : { name: "client.orders" },
                );
                return;
            }
            this.error = result.error || "Ошибка входа";
        },
    },
};
</script>

<template>
    <div>
        <div class="mb-4 flex gap-1 bg-white/60 p-1 dark:bg-gray-800/60">
            <router-link
                :to="{ name: 'client.login' }"
                class="flex-1 bg-[#C20A6C] px-3 py-3 text-center font-jost-bold text-base uppercase text-white"
            >
                Вход
            </router-link>
            <router-link
                :to="{ name: 'client.register' }"
                class="flex-1 px-3 py-3 text-center font-jost-bold text-base uppercase text-dark-gray-500 dark:text-gray-200"
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
                    autocomplete="current-password"
                    :class="fieldClass"
                />
            </label>

            <button
                type="submit"
                class="w-full bg-[#C20A6C] px-6 py-3.5 font-jost-bold text-base uppercase text-white disabled:opacity-50"
                :disabled="authStore.isLoading"
            >
                {{ authStore.isLoading ? "Вход…" : "Войти" }}
            </button>
        </form>
    </div>
</template>
