<template>
    <div class="client-auth-button">
        <!-- Не авторизован -->
        <div v-if="!isAuthenticated" class="flex items-center space-x-2">
            <button
                @click="showAuthModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-accent hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-colors duration-200"
            >
                <i class="mdi mdi-account mr-2"></i>
                Войти
            </button>
        </div>

        <!-- Авторизован -->
        <div v-else class="flex items-center space-x-2">
            <div class="flex items-center space-x-2">
                <span
                    class="text-sm text-gray-700 dark:text-gray-300 hidden md:block"
                >
                    {{ client?.full_name }}
                </span>
                <div class="relative">
                    <button
                        @click="showProfileDropdown = !showProfileDropdown"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-colors duration-200"
                    >
                        <i class="mdi mdi-account-circle mr-2"></i>
                        <span class="hidden sm:block">Профиль</span>
                        <i class="mdi mdi-chevron-down ml-1"></i>
                    </button>

                    <!-- Выпадающее меню -->
                    <div
                        v-if="showProfileDropdown"
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700"
                    >
                        <div
                            class="px-4 py-2 border-b border-gray-200 dark:border-gray-700"
                        >
                            <p
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{ client?.full_name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ client?.phone }}
                            </p>
                            <div class="mt-1">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                                        client?.is_telegram_verified
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    ]"
                                >
                                    <i
                                        :class="[
                                            'mr-1',
                                            client?.is_telegram_verified
                                                ? 'mdi mdi-check-circle'
                                                : 'mdi mdi-alert-circle',
                                        ]"
                                    ></i>
                                    {{
                                        client?.is_telegram_verified
                                            ? "Подтвержден"
                                            : "Требует верификации"
                                    }}
                                </span>
                            </div>
                        </div>

                        <button
                            @click="handleEditProfile"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                        >
                            <i class="mdi mdi-account-edit mr-2"></i>
                            Редактировать профиль
                        </button>

                        <a
                            href="https://t.me/zatochkatsk_bot"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block w-full text-left px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-200"
                        >
                            <i class="mdi mdi-telegram mr-2"></i>
                            Telegram Бот
                        </a>

                        <button
                            @click="handleLogout"
                            class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200"
                        >
                            <i class="mdi mdi-logout mr-2"></i>
                            Выйти
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно аутентификации -->
        <modal
            v-if="showAuthModal"
            :show="showAuthModal"
            title="Вход в систему"
            size="md"
            @close="showAuthModal = false"
        >
            <client-auth
                @auth-success="handleAuthSuccess"
                @logout="handleLogout"
                @verification-complete="handleVerificationComplete"
                @edit-profile="handleEditProfile"
            />
        </modal>

        <!-- Модальное окно редактирования профиля -->
        <modal
            v-if="showProfileModal"
            :show="showProfileModal"
            title="Редактирование профиля"
            size="md"
            @close="showProfileModal = false"
        >
            <client-profile-form
                :client="client"
                @profile-updated="handleProfileUpdated"
            />
        </modal>
    </div>
</template>

<script>
import clientAuthService from "../services/clientAuthService.js";
import Modal from "./Modal.vue";
import ClientAuth from "./auth/ClientAuth.vue";
import ClientProfileForm from "./auth/ClientProfileForm.vue";

export default {
    name: "ClientAuthButton",
    components: {
        Modal,
        ClientAuth,
        ClientProfileForm,
    },
    data() {
        return {
            client: null,
            showAuthModal: false,
            showProfileModal: false,
            showProfileDropdown: false,
        };
    },
    computed: {
        isAuthenticated() {
            return clientAuthService.isAuthenticated() && this.client;
        },
    },
    async mounted() {
        await this.checkAuthStatus();

        // Закрываем выпадающее меню при клике вне его
        document.addEventListener("click", this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutside);
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
            }
        },

        handleClickOutside(event) {
            if (!this.$el.contains(event.target)) {
                this.showProfileDropdown = false;
            }
        },

        async handleAuthSuccess(data) {
            this.client = data.client;
            this.showAuthModal = false;
            this.$emit("auth-success", data);
        },

        async handleLogout() {
            try {
                await clientAuthService.logout();
                this.client = null;
                this.showAuthModal = false;
                this.showProfileModal = false;
                this.showProfileDropdown = false;
                this.$emit("logout");
            } catch (error) {
                console.error("Logout error:", error);
                this.client = null;
                this.$emit("logout");
            }
        },

        async handleVerificationComplete() {
            await this.checkAuthStatus();
            this.$emit("verification-complete");
        },

        handleEditProfile() {
            this.showProfileModal = true;
            this.showProfileDropdown = false;
        },

        async handleProfileUpdated(updatedClient) {
            this.client = updatedClient;
            this.showProfileModal = false;
            this.$emit("profile-updated", updatedClient);
        },
    },
};
</script>
