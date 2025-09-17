<script>
import { object, string } from "yup";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "LoginForm",
    data() {
        return {
            form: {
                phone: "",
                password: "",
            },
            schema: object({
                phone: string().required("Номер телефона обязателен"),
                password: string().required("Пароль обязателен"),
            }),
            errors: {},
        };
    },
    computed: {
        authStore() {
            return useAuthStore();
        },
        isSubmitting() {
            return this.authStore.isLoading;
        },
    },
    methods: {
        async handleSubmit() {
            this.errors = {};

            try {
                await this.schema.validate(this.form, { abortEarly: false });

                const result = await this.authStore.login(this.form);

                if (result.success) {
                    // Успешная авторизация - форма переключится автоматически через Auth.vue
                    this.$emit("success");
                } else {
                    this.errors.general = result.error;
                }
            } catch (error) {
                if (error.inner) {
                    error.inner.forEach((err) => {
                        this.errors[err.path] = err.message;
                    });
                }
            }
        },
    },
};
</script>

<template>
    <div class="max-w-md mx-auto">
        <!-- Общие ошибки -->
        <div
            v-if="errors.general"
            class="mb-6 bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-6 py-4 rounded-2xl dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400"
        >
            {{ errors.general }}
        </div>

        <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">
            <div>
                <label
                    class="block text-lg font-medium text-gray-700 mb-3 dark:text-gray-300"
                    >Номер телефона</label
                >
                <input
                    type="tel"
                    v-model="form.phone"
                    placeholder="+7 (999) 123-45-67"
                    v-maska
                    data-maska="+7 (###) ###-##-##"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-800/60 dark:border-gray-700/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                    :class="{
                        'border-red-500/50 focus:ring-red-500/50 focus:border-red-500/50':
                            errors.phone,
                    }"
                />
                <div
                    v-if="errors.phone"
                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                >
                    {{ errors.phone }}
                </div>
            </div>

            <div>
                <label
                    class="block text-lg font-medium text-gray-700 mb-3 dark:text-gray-300"
                    >Пароль</label
                >
                <input
                    type="password"
                    v-model="form.password"
                    placeholder="Введите пароль"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-800/60 dark:border-gray-700/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                    :class="{
                        'border-red-500/50 focus:ring-red-500/50 focus:border-red-500/50':
                            errors.password,
                    }"
                />
                <div
                    v-if="errors.password"
                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                >
                    {{ errors.password }}
                </div>
            </div>

            <button
                type="submit"
                :disabled="isSubmitting"
                class="bg-blue-600/90 backdrop-blur-xs hover:bg-blue-700/90 text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-blue-500/90 dark:hover:bg-blue-600/90 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ isSubmitting ? "Вход..." : "Войти" }}
            </button>
        </form>
    </div>
</template>

<style scoped></style>
