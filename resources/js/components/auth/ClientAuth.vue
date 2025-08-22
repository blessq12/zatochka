<template>
    <div class="client-auth">
        <!-- Загрузка -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="text-center">
                <i
                    class="mdi mdi-loading mdi-spin text-4xl text-accent mb-4"
                ></i>
                <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
            </div>
        </div>

        <!-- Не авторизован -->
        <div v-else-if="!isAuthenticated" class="space-y-6">
            <!-- Переключение между формами -->
            <div
                class="flex space-x-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg"
            >
                <button
                    @click="currentForm = 'login'"
                    :class="[
                        'flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors duration-200',
                        currentForm === 'login'
                            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white',
                    ]"
                >
                    Вход
                </button>
                <button
                    @click="currentForm = 'register'"
                    :class="[
                        'flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors duration-200',
                        currentForm === 'register'
                            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white',
                    ]"
                >
                    Регистрация
                </button>
            </div>

            <!-- Форма входа -->
            <client-login-form
                v-if="currentForm === 'login'"
                @login-success="handleLoginSuccess"
                @show-register="currentForm = 'register'"
                @forgot-password="showForgotPassword = true"
            />

            <!-- Форма регистрации -->
            <client-register-form
                v-if="currentForm === 'register'"
                @register-success="handleRegisterSuccess"
                @show-login="currentForm = 'login'"
            />
        </div>

        <!-- Авторизован, но не верифицирован -->
        <div v-else-if="!client?.is_telegram_verified" class="space-y-6">
            <client-telegram-verification
                :client="client"
                @verification-complete="handleVerificationComplete"
            />
        </div>

        <!-- Полностью авторизован -->
        <div v-else class="space-y-6">
            <div class="text-center">
                <div class="mb-4">
                    <i
                        class="mdi mdi-account-check text-green-500 text-6xl"
                    ></i>
                </div>
                <h2
                    class="text-2xl font-bold text-gray-900 dark:text-white mb-2"
                >
                    Добро пожаловать!
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Ваш аккаунт полностью подтвержден
                </p>
            </div>

            <!-- Информация о клиенте -->
            <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                <h3
                    class="text-lg font-medium text-gray-900 dark:text-white mb-4"
                >
                    Информация об аккаунте
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400"
                            >ФИО:</span
                        >
                        <span
                            class="font-medium text-gray-900 dark:text-white"
                            >{{ client.full_name }}</span
                        >
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400"
                            >Телефон:</span
                        >
                        <span
                            class="font-medium text-gray-900 dark:text-white"
                            >{{ client.phone }}</span
                        >
                    </div>
                    <div v-if="client.telegram" class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400"
                            >Telegram:</span
                        >
                        <span
                            class="font-medium text-gray-900 dark:text-white"
                            >{{ client.telegram }}</span
                        >
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400"
                            >Статус:</span
                        >
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                        >
                            <i class="mdi mdi-check-circle mr-1"></i>
                            Подтвержден
                        </span>
                    </div>
                </div>
            </div>

            <!-- Информация о Telegram боте -->
            <div class="mt-6">
                <telegram-bot-info />
            </div>

            <!-- Действия -->
            <div class="flex space-x-3 mt-6">
                <button
                    @click="handleLogout"
                    class="flex-1 flex justify-center items-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-colors duration-200"
                >
                    <i class="mdi mdi-logout mr-2"></i>
                    Выйти
                </button>
                <button
                    @click="handleEditProfile"
                    class="flex-1 flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-accent hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-colors duration-200"
                >
                    <i class="mdi mdi-account-edit mr-2"></i>
                    Редактировать профиль
                </button>
            </div>
        </div>

        <!-- Модальное окно сброса пароля -->
        <modal
            v-if="showForgotPassword"
            :show="showForgotPassword"
            title="Сброс пароля"
            @close="showForgotPassword = false"
        >
            <div class="space-y-4">
                <p class="text-gray-600 dark:text-gray-400">
                    Введите номер телефона, указанный при регистрации. Мы
                    отправим инструкции для сброса пароля.
                </p>

                <div>
                    <label
                        for="reset_phone"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Номер телефона
                    </label>
                    <input
                        id="reset_phone"
                        v-model="resetPhone"
                        type="tel"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                        placeholder="+7 (999) 123-45-67"
                    />
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        @click="showForgotPassword = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-colors duration-200"
                    >
                        Отмена
                    </button>
                    <button
                        @click="handleForgotPassword"
                        :disabled="resettingPassword"
                        class="px-4 py-2 text-sm font-medium text-white bg-accent hover:bg-accent/90 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                    >
                        <span
                            v-if="resettingPassword"
                            class="flex items-center"
                        >
                            <i class="mdi mdi-loading mdi-spin mr-2"></i>
                            Отправка...
                        </span>
                        <span v-else>Отправить</span>
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
import clientAuthService from "../../services/clientAuthService.js";
import Modal from "../Modal.vue";
import TelegramBotInfo from "../TelegramBotInfo.vue";
import ClientLoginForm from "./ClientLoginForm.vue";
import ClientRegisterForm from "./ClientRegisterForm.vue";
import ClientTelegramVerification from "./ClientTelegramVerification.vue";

export default {
    name: "ClientAuth",
    components: {
        ClientLoginForm,
        ClientRegisterForm,
        ClientTelegramVerification,
        Modal,
        TelegramBotInfo,
    },
    data() {
        return {
            loading: true,
            currentForm: "login",
            client: null,
            showForgotPassword: false,
            resetPhone: "",
            resettingPassword: false,
        };
    },
    computed: {
        isAuthenticated() {
            return clientAuthService.isAuthenticated() && this.client;
        },
    },
    async mounted() {
        await this.checkAuthStatus();
    },
    methods: {
        async checkAuthStatus() {
            try {
                if (clientAuthService.isAuthenticated()) {
                    const response = await clientAuthService.checkToken();
                    this.client = response.data.client;
                }
            } catch (error) {
                console.error("Auth check error:", error);
                clientAuthService.removeToken();
            } finally {
                this.loading = false;
            }
        },

        async handleLoginSuccess(data) {
            this.client = data.client;
            this.$emit("auth-success", data);
        },

        async handleRegisterSuccess(data) {
            this.client = data.client;
            this.$emit("auth-success", data);
        },

        async handleVerificationComplete() {
            // Обновляем данные клиента
            await this.checkAuthStatus();
            this.$emit("verification-complete");
        },

        async handleLogout() {
            try {
                await clientAuthService.logout();
                this.client = null;
                this.$emit("logout");

                if (window.modalService) {
                    window.modalService.alert(
                        "Выход",
                        "Вы успешно вышли из системы",
                        "info"
                    );
                }
            } catch (error) {
                console.error("Logout error:", error);
                this.client = null;
                this.$emit("logout");
            }
        },

        handleEditProfile() {
            // Здесь можно открыть модальное окно редактирования профиля
            this.$emit("edit-profile", this.client);
        },

        async handleForgotPassword() {
            if (!this.resetPhone) {
                if (window.modalService) {
                    window.modalService.alert(
                        "Ошибка",
                        "Введите номер телефона",
                        "error"
                    );
                }
                return;
            }

            this.resettingPassword = true;

            try {
                await clientAuthService.forgotPassword(this.resetPhone);
                this.showForgotPassword = false;
                this.resetPhone = "";

                if (window.modalService) {
                    window.modalService.alert(
                        "Успех",
                        "Инструкции для сброса пароля отправлены на ваш телефон",
                        "success"
                    );
                }
            } catch (error) {
                console.error("Forgot password error:", error);

                if (window.modalService) {
                    window.modalService.alert(
                        "Ошибка",
                        error.message || "Не удалось отправить инструкции",
                        "error"
                    );
                }
            } finally {
                this.resettingPassword = false;
            }
        },
    },
};
</script>
