<script>
import { object, string } from "yup";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "RegisterForm",
    data() {
        return {
            form: {
                full_name: "",
                email: "",
                phone: "",
                password: "",
                passwordConfirmation: "",
            },
            schema: object({
                full_name: string().required("Имя обязательно"),
                email: string()
                    .email("Некорректный email")
                    .required("Email обязателен"),
                phone: string().required("Номер телефона обязателен"),
                password: string()
                    .min(6, "Пароль должен содержать минимум 6 символов")
                    .required("Пароль обязателен"),
                passwordConfirmation: string().required(
                    "Подтверждение пароля обязательно"
                ),
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
                if (this.form.password !== this.form.passwordConfirmation) {
                    this.errors.passwordConfirmation = "Пароли не совпадают";
                    return;
                }

                await this.schema.validate(this.form, { abortEarly: false });

                const registerData = {
                    fullName: this.form.full_name,
                    email: this.form.email,
                    phone: this.form.phone,
                    password: this.form.password,
                    passwordConfirmation: this.form.passwordConfirmation,
                };

                const result = await this.authStore.register(registerData);

                if (result.success) {
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
                    >Имя</label
                >
                <input
                    type="text"
                    v-model="form.full_name"
                    placeholder="Введите ваше имя"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-800/60 dark:border-gray-700/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                    :class="{
                        'border-red-500/50 focus:ring-red-500/50 focus:border-red-500/50':
                            errors.full_name,
                    }"
                />
                <div
                    v-if="errors.full_name"
                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                >
                    {{ errors.full_name }}
                </div>
            </div>

            <div>
                <label
                    class="block text-lg font-medium text-gray-700 mb-3 dark:text-gray-300"
                    >Email</label
                >
                <input
                    type="email"
                    v-model="form.email"
                    placeholder="example@email.com"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-800/60 dark:border-gray-700/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                    :class="{
                        'border-red-500/50 focus:ring-red-500/50 focus:border-red-500/50':
                            errors.email,
                    }"
                />
                <div
                    v-if="errors.email"
                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                >
                    {{ errors.email }}
                </div>
            </div>

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
                    placeholder="Минимум 6 символов"
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

            <div>
                <label
                    class="block text-lg font-medium text-gray-700 mb-3 dark:text-gray-300"
                    >Подтверждение пароля</label
                >
                <input
                    type="password"
                    v-model="form.passwordConfirmation"
                    placeholder="Повторите пароль"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-800/60 dark:border-gray-700/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                    :class="{
                        'border-red-500/50 focus:ring-red-500/50 focus:border-red-500/50':
                            errors.passwordConfirmation,
                    }"
                />
                <div
                    v-if="errors.passwordConfirmation"
                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                >
                    {{ errors.passwordConfirmation }}
                </div>
            </div>

            <button
                type="submit"
                :disabled="isSubmitting"
                class="bg-pink-600/90 backdrop-blur-xs hover:bg-pink-700/90 text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-pink-500/90 dark:hover:bg-pink-600/90 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ isSubmitting ? "Регистрация..." : "Зарегистрироваться" }}
            </button>
        </form>
    </div>
</template>

<style scoped></style>
