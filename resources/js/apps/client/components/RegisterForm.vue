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
    <div
        class="flex min-h-screen items-center justify-center bg-gradient-to-br from-dark-blue-500 via-blue-500 to-pink-500 px-4 py-8"
    >
        <form
            class="w-full max-w-md space-y-5 border border-white/40 bg-white p-8 shadow-2xl sm:p-10"
            @submit.prevent="submit"
        >
            <div>
                <h1 class="text-2xl font-jost-bold text-dark-blue-500 sm:text-3xl">
                    Регистрация
                </h1>
                <p class="mt-2 text-base text-slate-600">
                    Создайте кабинет клиента
                </p>
            </div>

            <p v-if="error" class="bg-red-50 px-4 py-3 text-base text-red-700">
                {{ error }}
            </p>

            <div>
                <label class="mb-2 block text-base font-jost-medium text-slate-700">
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    class="w-full border border-slate-400 px-3 py-3 text-base text-dark-blue-500 outline-none focus:border-pink-500"
                />
            </div>

            <div>
                <label class="mb-2 block text-base font-jost-medium text-slate-700">
                    Пароль
                </label>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full border border-slate-400 px-3 py-3 text-base text-dark-blue-500 outline-none focus:border-pink-500"
                />
            </div>

            <div>
                <label class="mb-2 block text-base font-jost-medium text-slate-700">
                    Повтор пароля
                </label>
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full border border-slate-400 px-3 py-3 text-base text-dark-blue-500 outline-none focus:border-pink-500"
                />
            </div>

            <button
                type="submit"
                class="w-full bg-pink-500 py-3 text-base font-jost-bold text-white hover:bg-pink-600 disabled:opacity-50"
                :disabled="authStore.isLoading"
            >
                {{ authStore.isLoading ? "Регистрация…" : "Зарегистрироваться" }}
            </button>

            <button
                type="button"
                class="w-full text-base text-slate-600 hover:text-pink-600"
                @click="$router.push({ name: 'client.login' })"
            >
                Уже есть аккаунт? Войти
            </button>
        </form>
    </div>
</template>
