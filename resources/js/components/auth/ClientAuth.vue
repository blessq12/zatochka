<template>
    <div class="client-auth" ref="container">
        <div
            v-if="loading"
            class="flex items-center justify-center py-12"
            ref="loadingState"
        >
            <div class="text-center">
                <i
                    class="mdi mdi-loading mdi-spin text-4xl text-accent mb-4"
                ></i>
                <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
            </div>
        </div>

        <!-- Не авторизован -->
        <div v-else-if="!isAuthenticated" class="space-y-6" ref="authForms">
            <!-- Переключение между формами -->
            <div
                class="flex space-x-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl shadow-inner"
                ref="formTabs"
            >
                <button
                    @click="switchForm('login')"
                    :class="[
                        'flex-1 py-3 px-4 text-sm font-medium rounded-lg transition-all duration-300 transform',
                        currentForm === 'login'
                            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-md scale-105'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:scale-102',
                    ]"
                    ref="loginTab"
                >
                    <i class="mdi mdi-login mr-2"></i>
                    Вход
                </button>
                <button
                    @click="switchForm('register')"
                    :class="[
                        'flex-1 py-3 px-4 text-sm font-medium rounded-lg transition-all duration-300 transform',
                        currentForm === 'register'
                            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-md scale-105'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:scale-102',
                    ]"
                    ref="registerTab"
                >
                    <i class="mdi mdi-account-plus mr-2"></i>
                    Регистрация
                </button>
            </div>

            <!-- Форма входа -->
            <div v-if="currentForm === 'login'" ref="loginFormContainer">
                <client-login-form
                    @login-success="handleLoginSuccess"
                    @show-register="switchForm('register')"
                    @forgot-password="showForgotPassword = true"
                />
            </div>

            <!-- Форма регистрации -->
            <div v-if="currentForm === 'register'" ref="registerFormContainer">
                <client-register-form
                    @register-success="handleRegisterSuccess"
                    @show-login="switchForm('login')"
                />
            </div>
        </div>

        <!-- Авторизован, но не верифицирован -->
        <div
            v-else-if="client && !client?.is_telegram_verified"
            class="space-y-6"
            ref="verificationContainer"
        >
            <!-- Режим редактирования -->
            <div v-if="isEditing" class="space-y-6">
                <client-profile-edit
                    :client="client"
                    @profile-updated="handleProfileUpdated"
                    @cancel="isEditing = false"
                />
            </div>

            <!-- Обычный режим просмотра -->
            <div v-else class="space-y-6">
                <!-- Информация о клиенте -->
                <div
                    class="bg-gray-50 dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-md"
                >
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
                        <div
                            v-if="client.telegram"
                            class="flex justify-between"
                        >
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
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
                            >
                                <i class="mdi mdi-alert-circle mr-1"></i>
                                Требуется верификация
                            </span>
                        </div>
                        <div
                            v-if="client.created_at"
                            class="flex justify-between"
                        >
                            <span class="text-gray-600 dark:text-gray-400"
                                >Дата регистрации:</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{{ formatDate(client.created_at) }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Верификация Telegram -->
                <div
                    class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-200 dark:border-blue-800"
                >
                    <h4
                        class="font-medium text-blue-900 dark:text-blue-100 mb-2"
                    >
                        Верификация Telegram
                    </h4>
                    <p class="text-sm text-blue-800 dark:text-blue-200 mb-4">
                        Для полного доступа к системе необходимо подтвердить
                        Telegram аккаунт
                    </p>
                    <button
                        @click="showTelegramVerification = true"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center"
                    >
                        <i class="mdi mdi-telegram mr-2"></i>
                        Пройти верификацию
                    </button>
                </div>

                <!-- Действия -->
                <div class="flex space-x-3">
                    <button
                        @click="handleLogout"
                        class="flex-1 flex justify-center items-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all duration-200 transform hover:scale-105"
                    >
                        <i class="mdi mdi-logout mr-2"></i>
                        Выйти
                    </button>
                    <button
                        @click="handleEditProfile"
                        class="flex-1 flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-gradient-to-r from-accent to-pink-600 hover:from-accent/90 hover:to-pink-600/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all duration-200 transform hover:scale-105"
                    >
                        <i class="mdi mdi-account-edit mr-2"></i>
                        Редактировать профиль
                    </button>
                </div>
            </div>
        </div>

        <!-- Авторизован, но данные еще загружаются -->
        <div
            v-else-if="isAuthenticated && !client"
            class="flex items-center justify-center py-12"
        >
            <div class="text-center">
                <i
                    class="mdi mdi-loading mdi-spin text-4xl text-accent mb-4"
                ></i>
                <p class="text-gray-600 dark:text-gray-400">
                    Загрузка профиля...
                </p>
            </div>
        </div>

        <!-- Полностью авторизован -->
        <div
            v-else-if="client && client?.is_telegram_verified"
            class="space-y-6"
            ref="authenticatedContainer"
        >
            <!-- Режим редактирования -->
            <div v-if="isEditing" class="space-y-6">
                <client-profile-edit
                    :client="client"
                    @profile-updated="handleProfileUpdated"
                    @cancel="isEditing = false"
                />
            </div>

            <!-- Обычный режим просмотра -->
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
                <div
                    class="bg-gray-50 dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-md"
                >
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
                        <div
                            v-if="client.telegram"
                            class="flex justify-between"
                        >
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
                        <div
                            v-if="client.created_at"
                            class="flex justify-between"
                        >
                            <span class="text-gray-600 dark:text-gray-400"
                                >Дата регистрации:</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{{ formatDate(client.created_at) }}</span
                            >
                        </div>
                        <div
                            v-if="client.telegram_verified_at"
                            class="flex justify-between"
                        >
                            <span class="text-gray-600 dark:text-gray-400"
                                >Верифицирован:</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{{
                                    formatDate(client.telegram_verified_at)
                                }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Действия -->
                <div class="flex space-x-3 mt-6">
                    <button
                        @click="handleLogout"
                        class="flex-1 flex justify-center items-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all duration-200 transform hover:scale-105"
                    >
                        <i class="mdi mdi-logout mr-2"></i>
                        Выйти
                    </button>
                    <button
                        @click="handleEditProfile"
                        class="flex-1 flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-gradient-to-r from-accent to-pink-600 hover:from-accent/90 hover:to-pink-600/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all duration-200 transform hover:scale-105"
                    >
                        <i class="mdi mdi-account-edit mr-2"></i>
                        Редактировать профиль
                    </button>
                </div>
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
                    Введите номер телефона для сброса пароля:
                </p>
                <input
                    v-model="resetPhone"
                    type="tel"
                    class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                    placeholder="+7 (___) ___-__-__"
                    v-maska
                    data-maska="+7 (###) ###-##-##"
                />
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showForgotPassword = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-colors duration-200"
                    >
                        Отмена
                    </button>
                    <button
                        @click="handleForgotPassword"
                        :disabled="resettingPassword"
                        class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-accent to-pink-600 hover:from-accent/90 hover:to-pink-600/90 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
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

        <!-- Модальное окно верификации Telegram -->
        <TelegramVerificationModal
            :show="showTelegramVerification"
            :client="client"
            @close="showTelegramVerification = false"
            @verification-complete="handleVerificationComplete"
        />
    </div>
</template>

<script>
import { gsap } from "gsap";
import { useAuthStore } from "../../stores/auth.js";
import Modal from "../Modal.vue";
import TelegramVerificationModal from "../modals/TelegramVerificationModal.vue";
import TelegramBotInfo from "../TelegramBotInfo.vue";
import ClientLoginForm from "./ClientLoginForm.vue";
import ClientProfileEdit from "./ClientProfileEdit.vue";
import ClientRegisterForm from "./ClientRegisterForm.vue";

export default {
    name: "ClientAuth",
    components: {
        ClientLoginForm,
        ClientRegisterForm,
        TelegramVerificationModal,
        Modal,
        TelegramBotInfo,
        ClientProfileEdit,
    },
    data() {
        return {
            currentForm: "login",
            showForgotPassword: false,
            resetPhone: "",
            resettingPassword: false,
            isEditing: false,
            showTelegramVerification: false,
        };
    },
    computed: {
        authStore() {
            return useAuthStore();
        },
        loading() {
            return this.authStore.getLoading;
        },
        client() {
            return this.authStore.getUser;
        },
        isAuthenticated() {
            const authenticated = this.authStore.isAuthenticated;
            console.log("🔍 ClientAuth isAuthenticated:", {
                storeAuthenticated: this.authStore.isAuthenticated,
                storeUser: this.authStore.getUser,
                result: authenticated,
            });
            return authenticated;
        },
    },
    async mounted() {
        // Инициализируем стор если нужно
        if (this.authStore.isAuthenticated && !this.authStore.getUser) {
            await this.checkAuthStatus();
        }

        // Анимация появления компонента
        this.$nextTick(() => {
            this.animateComponentEnter();
        });
    },
    methods: {
        // Анимация появления компонента
        animateComponentEnter() {
            if (this.loading && this.$refs.loadingState) {
                gsap.fromTo(
                    this.$refs.loadingState,
                    {
                        opacity: 0,
                        scale: 0.9,
                    },
                    {
                        opacity: 1,
                        scale: 1,
                        duration: 0.5,
                        ease: "back.out(1.7)",
                    }
                );
            } else if (!this.isAuthenticated && this.$refs.authForms) {
                this.animateAuthForms();
            } else if (
                !this.client?.is_telegram_verified &&
                this.$refs.verificationContainer
            ) {
                this.animateVerification();
            } else if (
                this.client?.is_telegram_verified &&
                this.$refs.authenticatedContainer
            ) {
                this.animateAuthenticated();
            }
        },

        // Анимация форм авторизации
        animateAuthForms() {
            if (!this.$refs.authForms) return;

            gsap.fromTo(
                this.$refs.authForms,
                {
                    opacity: 0,
                    y: 30,
                    scale: 0.95,
                },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.6,
                    ease: "back.out(1.7)",
                }
            );

            // Анимация табов
            if (this.$refs.formTabs) {
                gsap.fromTo(
                    this.$refs.formTabs,
                    {
                        opacity: 0,
                        y: -20,
                    },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.4,
                        ease: "back.out(1.7)",
                        delay: 0.2,
                    }
                );
            }
        },

        // Анимация верификации
        animateVerification() {
            if (!this.$refs.verificationContainer) return;

            gsap.fromTo(
                this.$refs.verificationContainer,
                {
                    opacity: 0,
                    y: 30,
                    scale: 0.95,
                },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.6,
                    ease: "back.out(1.7)",
                }
            );
        },

        // Анимация авторизованного состояния
        animateAuthenticated() {
            if (!this.$refs.authenticatedContainer) return;

            gsap.fromTo(
                this.$refs.authenticatedContainer,
                {
                    opacity: 0,
                    y: 30,
                    scale: 0.95,
                },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.6,
                    ease: "back.out(1.7)",
                }
            );
        },

        // Анимация переключения форм
        switchForm(form) {
            if (form === this.currentForm) return;

            const oldForm = this.currentForm;
            this.currentForm = form;

            // Анимация табов
            gsap.to(this.$refs.loginTab, {
                scale: form === "login" ? 1.05 : 1,
                duration: 0.3,
                ease: "back.out(1.7)",
            });

            gsap.to(this.$refs.registerTab, {
                scale: form === "register" ? 1.05 : 1,
                duration: 0.3,
                ease: "back.out(1.7)",
            });

            // Анимация перехода между формами
            if (oldForm === "login" && form === "register") {
                gsap.to(this.$refs.loginFormContainer, {
                    opacity: 0,
                    x: -50,
                    duration: 0.3,
                    ease: "power2.in",
                    onComplete: () => {
                        gsap.fromTo(
                            this.$refs.registerFormContainer,
                            {
                                opacity: 0,
                                x: 50,
                            },
                            {
                                opacity: 1,
                                x: 0,
                                duration: 0.3,
                                ease: "power2.out",
                            }
                        );
                    },
                });
            } else if (oldForm === "register" && form === "login") {
                gsap.to(this.$refs.registerFormContainer, {
                    opacity: 0,
                    x: 50,
                    duration: 0.3,
                    ease: "power2.in",
                    onComplete: () => {
                        gsap.fromTo(
                            this.$refs.loginFormContainer,
                            {
                                opacity: 0,
                                x: -50,
                            },
                            {
                                opacity: 1,
                                x: 0,
                                duration: 0.3,
                                ease: "power2.out",
                            }
                        );
                    },
                });
            }
        },

        async checkAuthStatus() {
            console.log("🔍 checkAuthStatus called");
            console.log("🔍 Current auth state:", {
                isAuthenticated: this.authStore.isAuthenticated,
                token: this.authStore.token,
                user: this.authStore.getUser,
            });

            try {
                // Если есть токен, проверяем его валидность
                if (this.authStore.isAuthenticated) {
                    console.log("🔍 Token exists, checking...");
                    await this.authStore.checkToken();
                    console.log("🔍 After checkToken:", {
                        isAuthenticated: this.authStore.isAuthenticated,
                        user: this.authStore.getUser,
                    });
                } else {
                    console.log("🔍 No token found");
                }
            } catch (error) {
                console.error("🔍 checkAuthStatus error:", error);
                // Если токен недействителен, он уже удален в сторе
            }
        },

        async handleLoginSuccess(data) {
            console.log("🎯 handleLoginSuccess called with:", data);
            console.log("🔍 Current auth state:", {
                isAuthenticated: this.isAuthenticated,
                client: this.client,
                loading: this.loading,
            });

            // Обновляем данные клиента из стора
            await this.checkAuthStatus();

            // Ждем следующего тика для обновления DOM
            await this.$nextTick();

            console.log("🔍 After checkAuthStatus:", {
                isAuthenticated: this.isAuthenticated,
                client: this.client,
                loading: this.loading,
            });

            // Эмитим событие
            this.$emit("auth-success", data);
        },

        async handleRegisterSuccess(data) {
            // Обновляем данные клиента из стора
            await this.checkAuthStatus();

            // Ждем следующего тика для обновления DOM
            await this.$nextTick();

            // Эмитим событие
            this.$emit("auth-success", data);
        },

        async handleVerificationComplete(clientData = null) {
            if (!clientData) {
                // Обновляем данные клиента через API
                await this.checkAuthStatus();
            }

            this.$emit("verification-complete");
        },

        async handleLogout() {
            try {
                await this.authStore.logout();

                // Эмитим событие об изменении статуса авторизации
                window.dispatchEvent(
                    new CustomEvent("auth-status-changed", {
                        detail: { isAuthenticated: false, client: null },
                    })
                );

                this.$emit("logout");
            } catch (error) {
                console.error("Logout error:", error);
            }
        },

        handleEditProfile() {
            this.isEditing = true;
        },

        handleProfileUpdated(updatedClient) {
            this.client = updatedClient;
            this.isEditing = false;

            // Эмитим событие об изменении статуса авторизации
            window.dispatchEvent(
                new CustomEvent("auth-status-changed", {
                    detail: { isAuthenticated: true, client: this.client },
                })
            );

            this.$emit("profile-updated", updatedClient);
        },

        async handleForgotPassword() {
            if (!this.resetPhone) {
                return;
            }

            this.resettingPassword = true;

            try {
                await this.authStore.forgotPassword({
                    phone: this.resetPhone,
                });
                this.showForgotPassword = false;
                this.resetPhone = "";
            } catch (error) {
                console.error("Forgot password error:", error);
            } finally {
                this.resettingPassword = false;
            }
        },

        formatDate(timestamp) {
            if (!timestamp) return "Неизвестно";
            const date = new Date(timestamp);
            return date.toLocaleDateString("ru-RU", {
                year: "numeric",
                month: "numeric",
                day: "numeric",
                hour: "numeric",
                minute: "numeric",
            });
        },
    },
};
</script>

<style scoped>
/* Дополнительные стили для анимаций */
.client-auth {
    transition: all 0.3s ease;
}

/* Эффект hover для кнопок */
button:hover {
    transform: translateY(-1px);
}

/* Анимация для иконок */
.mdi {
    transition: all 0.2s ease;
}

/* Эффект для карточек при hover */
.bg-gray-50:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.dark .bg-gray-800:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}
</style>
