<template>
    <div class="client-register-form">
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Заголовок -->
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Регистрация
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Создайте аккаунт для доступа к услугам
                </p>
            </div>

            <!-- ФИО -->
            <div>
                <label
                    for="full_name"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    ФИО *
                </label>
                <input
                    id="full_name"
                    v-model="form.full_name"
                    type="text"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                    placeholder="Иванов Иван Иванович"
                    :disabled="loading"
                />
                <div v-if="errors.full_name" class="mt-1 text-sm text-red-600">
                    {{ errors.full_name }}
                </div>
            </div>

            <!-- Номер телефона -->
            <div>
                <label
                    for="phone"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Номер телефона *
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

            <!-- Telegram -->
            <div>
                <label
                    for="telegram"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Telegram аккаунт
                </label>
                <input
                    id="telegram"
                    v-model="form.telegram"
                    type="text"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                    placeholder="@username или номер телефона"
                    :disabled="loading"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Для получения уведомлений и верификации аккаунта
                </p>
                <div v-if="errors.telegram" class="mt-1 text-sm text-red-600">
                    {{ errors.telegram }}
                </div>
            </div>

            <!-- Дата рождения -->
            <div>
                <label
                    for="birth_date"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Дата рождения
                </label>
                <input
                    id="birth_date"
                    v-model="form.birth_date"
                    type="date"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                    :disabled="loading"
                />
                <div v-if="errors.birth_date" class="mt-1 text-sm text-red-600">
                    {{ errors.birth_date }}
                </div>
            </div>

            <!-- Адрес доставки -->
            <div>
                <label
                    for="delivery_address"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Адрес доставки
                </label>
                <textarea
                    id="delivery_address"
                    v-model="form.delivery_address"
                    rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                    placeholder="Улица, дом, квартира"
                    :disabled="loading"
                ></textarea>
                <div
                    v-if="errors.delivery_address"
                    class="mt-1 text-sm text-red-600"
                >
                    {{ errors.delivery_address }}
                </div>
            </div>

            <!-- Пароль -->
            <div>
                <label
                    for="password"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Пароль *
                </label>
                <div class="relative">
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                        placeholder="Минимум 6 символов"
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

            <!-- Подтверждение пароля -->
            <div>
                <label
                    for="password_confirmation"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Подтверждение пароля *
                </label>
                <div class="relative">
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        required
                        class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                        placeholder="Повторите пароль"
                        :disabled="loading"
                    />
                    <button
                        type="button"
                        @click="
                            showPasswordConfirmation = !showPasswordConfirmation
                        "
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <i
                            :class="
                                showPasswordConfirmation
                                    ? 'mdi mdi-eye-off'
                                    : 'mdi mdi-eye'
                            "
                        ></i>
                    </button>
                </div>
                <div
                    v-if="errors.password_confirmation"
                    class="mt-1 text-sm text-red-600"
                >
                    {{ errors.password_confirmation }}
                </div>
            </div>

            <!-- Кнопка регистрации -->
            <button
                type="submit"
                :disabled="loading"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-accent hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
                <span v-if="loading" class="flex items-center">
                    <i class="mdi mdi-loading mdi-spin mr-2"></i>
                    Регистрация...
                </span>
                <span v-else>Зарегистрироваться</span>
            </button>

            <!-- Ссылка на вход -->
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Уже есть аккаунт?
                    <button
                        type="button"
                        @click="$emit('show-login')"
                        class="text-accent hover:text-accent/80 dark:text-accent-light dark:hover:text-accent-light/80 font-medium"
                        :disabled="loading"
                    >
                        Войти
                    </button>
                </p>
            </div>
        </form>
    </div>
</template>

<script>
import clientAuthService from "../../services/clientAuthService.js";

export default {
    name: "ClientRegisterForm",
    emits: ["register-success", "show-login"],
    data() {
        return {
            form: {
                full_name: "",
                phone: "",
                telegram: "",
                birth_date: "",
                delivery_address: "",
                password: "",
                password_confirmation: "",
            },
            errors: {},
            loading: false,
            showPassword: false,
            showPasswordConfirmation: false,
        };
    },
    methods: {
        async handleSubmit() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await clientAuthService.register(this.form);

                this.$emit("register-success", response.data);

                // Показываем уведомление об успешной регистрации
                if (window.modalService) {
                    window.modalService.alert(
                        "Успешная регистрация",
                        "Аккаунт создан! Добро пожаловать в систему!",
                        "success"
                    );
                }
            } catch (error) {
                console.error("Register error:", error);

                // Обрабатываем ошибки валидации
                if (error.message.includes("validation")) {
                    // Здесь можно парсить ошибки валидации из ответа сервера
                    this.errors.general =
                        "Проверьте правильность заполнения полей";
                } else {
                    this.errors.general =
                        error.message || "Произошла ошибка при регистрации";
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
