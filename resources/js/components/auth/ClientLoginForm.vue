<template>
    <div class="client-login-form">
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Заголовок -->
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Вход в аккаунт
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Войдите в свой аккаунт для доступа к услугам
                </p>
            </div>

            <!-- Номер телефона -->
            <div>
                <label
                    for="phone"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Номер телефона
                </label>
                <input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                    placeholder="+7 (999) 123-45-67"
                    :disabled="loading"
                />
                <div v-if="errors.phone" class="mt-1 text-sm text-red-600">
                    {{ errors.phone }}
                </div>
            </div>

            <!-- Пароль -->
            <div>
                <label
                    for="password"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Пароль
                </label>
                <div class="relative">
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                        placeholder="Введите пароль"
                        :disabled="loading"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <i
                            :class="
                                showPassword ? 'mdi mdi-eye-off' : 'mdi mdi-eye'
                            "
                        ></i>
                    </button>
                </div>
                <div v-if="errors.password" class="mt-1 text-sm text-red-600">
                    {{ errors.password }}
                </div>
            </div>

            <!-- Запомнить меня -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input
                        id="remember"
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded"
                        :disabled="loading"
                    />
                    <label
                        for="remember"
                        class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                    >
                        Запомнить меня
                    </label>
                </div>
                <button
                    type="button"
                    @click="$emit('forgot-password')"
                    class="text-sm text-accent hover:text-accent/80 dark:text-accent-light dark:hover:text-accent-light/80"
                    :disabled="loading"
                >
                    Забыли пароль?
                </button>
            </div>

            <!-- Кнопка входа -->
            <button
                type="submit"
                :disabled="loading"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-accent hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
                <span v-if="loading" class="flex items-center">
                    <i class="mdi mdi-loading mdi-spin mr-2"></i>
                    Вход...
                </span>
                <span v-else>Войти</span>
            </button>

            <!-- Ссылка на регистрацию -->
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Нет аккаунта?
                    <button
                        type="button"
                        @click="$emit('show-register')"
                        class="text-accent hover:text-accent/80 dark:text-accent-light dark:hover:text-accent-light/80 font-medium"
                        :disabled="loading"
                    >
                        Зарегистрироваться
                    </button>
                </p>
            </div>
        </form>
    </div>
</template>

<script>
import clientAuthService from "../../services/clientAuthService.js";

export default {
    name: "ClientLoginForm",
    emits: ["login-success", "show-register", "forgot-password"],
    data() {
        return {
            form: {
                phone: "",
                password: "",
                remember: false,
            },
            errors: {},
            loading: false,
            showPassword: false,
        };
    },
    methods: {
        async handleSubmit() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await clientAuthService.login(this.form);

                this.$emit("login-success", response.data);

                // Показываем уведомление об успешном входе
                if (window.modalService) {
                    window.modalService.alert(
                        "Успешный вход",
                        "Добро пожаловать в систему!",
                        "success"
                    );
                }
            } catch (error) {
                console.error("Login error:", error);

                // Обрабатываем ошибки валидации
                if (
                    error.message.includes("Неверный номер телефона или пароль")
                ) {
                    this.errors.phone = "Неверный номер телефона или пароль";
                } else {
                    this.errors.general =
                        error.message || "Произошла ошибка при входе";
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
