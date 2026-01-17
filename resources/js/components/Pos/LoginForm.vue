<script>
import { mapStores } from "pinia";
import * as yup from "yup";
import { usePosStore } from "../../stores/posStore.js";

export default {
    name: "PosLoginForm",
    data() {
        return {
            form: {
                email: "",
                password: "",
            },
            errors: {},
            schema: yup.object().shape({
                email: yup
                    .string()
                    .email("Неверный формат email")
                    .required("Email обязателен для заполнения"),
                password: yup
                    .string()
                    .required("Пароль обязателен для заполнения")
                    .min(6, "Пароль должен содержать минимум 6 символов"),
            }),
        };
    },
    computed: {
        ...mapStores(usePosStore),
    },
    methods: {
        async handleSubmit() {
            this.errors = {};

            try {
                await this.schema.validate(this.form, {
                    abortEarly: false,
                });

                const result = await this.posStore.login(this.form);

                if (result.success) {
                    this.$emit("login-success", this.posStore.user);
                } else {
                    this.errors.general = result.error || "Ошибка авторизации";
                }
            } catch (error) {
                if (error.inner) {
                    error.inner.forEach((err) => {
                        this.errors[err.path] = err.message;
                    });
                } else if (error.response?.data?.errors) {
                    this.errors = error.response.data.errors;
                } else {
                    this.errors.general =
                        error.response?.data?.message ||
                        "Ошибка авторизации. Проверьте правильность данных.";
                }
            }
        },
    },
};
</script>

<template>
    <div
        class="bg-white/85 backdrop-blur-2xl shadow-2xl p-10 sm:p-12 lg:p-16 border border-white/25 dark:bg-gray-800/90 dark:border-gray-600/30"
    >
        <h2
            class="text-2xl sm:text-3xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-8"
        >
            Вход в POS панель
        </h2>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Общая ошибка -->
            <div
                v-if="errors.general"
                class="bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-6 py-4 dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400"
            >
                {{ errors.general }}
            </div>

            <!-- Email -->
            <div>
                <label
                    class="block text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-3"
                >
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    placeholder="master@example.com"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-700/60 dark:border-gray-600/20"
                    :class="{
                        'border-red-500': errors.email,
                    }"
                />
                <p
                    v-if="errors.email"
                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                >
                    {{ errors.email }}
                </p>
            </div>

            <!-- Пароль -->
            <div>
                <label
                    class="block text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-3"
                >
                    Пароль
                </label>
                <input
                    v-model="form.password"
                    type="password"
                    placeholder="Введите пароль"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-700/60 dark:border-gray-600/20"
                    :class="{
                        'border-red-500': errors.password,
                    }"
                />
                <p
                    v-if="errors.password"
                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                >
                    {{ errors.password }}
                </p>
            </div>

            <!-- Кнопка отправки -->
            <button
                type="submit"
                :disabled="posStore.isLoading"
                class="w-full bg-[#C3006B] hover:bg-[#C3006B]/90 text-white px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span v-if="posStore.isLoading">Вход...</span>
                <span v-else>Войти</span>
            </button>
        </form>
    </div>
</template>

<style scoped></style>
