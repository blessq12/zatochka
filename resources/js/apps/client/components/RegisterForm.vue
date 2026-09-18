<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";

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
    <div class="min-h-dvh bg-white dark:bg-dark-blue-500">
        <section class="py-12 sm:py-16 lg:py-20">
            <div class="mx-auto max-w-2xl px-8 sm:px-12 lg:px-16">
                <a
                    href="/"
                    class="mb-8 inline-flex items-center gap-2 font-jost-medium text-dark-gray-500 transition-colors duration-300 hover:text-[#C3006B] dark:text-gray-200 dark:hover:text-[#C3006B] sm:mb-10"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                    На сайт
                </a>

                <div
                    class="mb-8 flex gap-4 border border-white/20 bg-white/60 p-2 backdrop-blur-md dark:border-gray-700/20 dark:bg-gray-800/60 sm:mb-12"
                >
                    <router-link
                        :to="{ name: 'client.login' }"
                        class="flex-1 px-6 py-4 text-center font-jost-bold text-lg text-dark-gray-500 transition-all duration-300 hover:bg-white/80 dark:text-gray-200 dark:hover:bg-gray-700/80"
                    >
                        Вход
                    </router-link>
                    <router-link
                        :to="{ name: 'client.register' }"
                        class="flex-1 bg-[#C3006B] px-6 py-4 text-center font-jost-bold text-lg text-white shadow-lg"
                    >
                        Регистрация
                    </router-link>
                </div>

                <form
                    class="border border-white/25 bg-white/85 p-10 shadow-2xl backdrop-blur-2xl dark:border-gray-600/30 dark:bg-gray-800/90 sm:p-12 lg:p-16"
                    @submit.prevent="submit"
                >
                    <h2
                        class="mb-8 text-2xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 sm:text-3xl"
                    >
                        Регистрация
                    </h2>

                    <div
                        v-if="error"
                        class="mb-6 border border-red-300/50 bg-red-50/80 px-6 py-4 text-red-700 backdrop-blur-lg dark:border-red-600/50 dark:bg-red-900/30 dark:text-red-400"
                    >
                        {{ error }}
                    </div>

                    <div class="mb-6">
                        <label
                            class="mb-3 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 sm:text-lg"
                        >
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="username"
                            class="w-full border border-dark-gray-500/30 bg-white/60 px-4 py-3 text-dark-blue-500 backdrop-blur-md outline-none focus:border-[#C3006B] dark:border-gray-600 dark:bg-gray-700/60 dark:text-gray-100"
                        />
                    </div>

                    <div class="mb-6">
                        <label
                            class="mb-3 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 sm:text-lg"
                        >
                            Пароль
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full border border-dark-gray-500/30 bg-white/60 px-4 py-3 text-dark-blue-500 backdrop-blur-md outline-none focus:border-[#C3006B] dark:border-gray-600 dark:bg-gray-700/60 dark:text-gray-100"
                        />
                    </div>

                    <div class="mb-8">
                        <label
                            class="mb-3 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 sm:text-lg"
                        >
                            Повтор пароля
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full border border-dark-gray-500/30 bg-white/60 px-4 py-3 text-dark-blue-500 backdrop-blur-md outline-none focus:border-[#C3006B] dark:border-gray-600 dark:bg-gray-700/60 dark:text-gray-100"
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-[#C3006B] px-10 py-5 font-jost-bold text-lg text-white shadow-lg transition-all duration-300 hover:bg-[#C3006B]/90 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50 sm:text-xl"
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
        </section>
    </div>
</template>
