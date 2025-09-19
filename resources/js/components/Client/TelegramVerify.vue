<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "TelegramVerify",
    data() {
        return {
            verificationCode: "",
            isCodeSent: false,
            isVerifying: false,
            isCheckingChat: false,
        };
    },
    computed: {
        ...mapStores(useAuthStore),
        userStatus() {
            if (!this.authStore.user) return "loading";

            const hasTelegram =
                this.authStore.user.telegram &&
                this.authStore.user.telegram.length > 0;
            const isVerified =
                this.authStore.user.telegram_verified_at !== null &&
                this.authStore.user.telegram_verified_at !== undefined;

            if (!hasTelegram) return "no-telegram";
            if (hasTelegram && !isVerified) return "unverified";
            if (hasTelegram && isVerified) return "verified";

            return "loading";
        },
        isTelegramConnected() {
            return this.userStatus === "verified";
        },
    },
    mounted() {
        if (this.authStore.user) {
            console.log("Данные пользователя:", this.authStore.user);
            console.log("telegram:", this.authStore.user.telegram);
            console.log(
                "telegram_verified_at:",
                this.authStore.user.telegram_verified_at
            );
            console.log("Статус пользователя:", this.userStatus);
        }
    },
    methods: {
        async connectTelegram() {
            console.log("Подключение Telegram...");
            // TODO: Реализовать логику подключения Telegram
        },

        async sendVerificationCode() {
            this.isCheckingChat = true;
            try {
                // Сначала проверяем наличие чата
                const chatResult = await this.authStore.checkTelegramChat();

                if (!chatResult.success) {
                    console.error("Ошибка проверки чата:", chatResult.error);
                    return;
                }

                if (!chatResult.data.chatExists) {
                    // Чат не найден - показываем предупреждение
                    console.log(
                        "Чат не найден для пользователя:",
                        this.authStore.user?.telegram
                    );
                    return;
                }

                // Чат найден - отправляем код подтверждения
                this.isVerifying = true;
                const codeResult =
                    await this.authStore.sendTelegramVerificationCode();

                if (codeResult.success) {
                    this.isCodeSent = true;
                    this.verificationCode = ""; // Очищаем поле
                }
            } catch (error) {
                console.error("Ошибка отправки кода:", error);
            } finally {
                this.isVerifying = false;
                this.isCheckingChat = false;
            }
        },

        async verifyCode() {
            if (!this.verificationCode.trim()) return;

            this.isVerifying = true;
            try {
                const result = await this.authStore.verifyTelegramCode(
                    this.verificationCode.trim()
                );

                if (result.success) {
                    // Код подтвержден успешно
                    this.isCodeSent = false;
                    this.verificationCode = "";
                    console.log("Telegram успешно подтвержден!");
                } else {
                    console.error("Ошибка подтверждения кода:", result.error);
                }
            } catch (error) {
                console.error("Ошибка проверки кода:", error);
            } finally {
                this.isVerifying = false;
            }
        },

        async resendCode() {
            this.isCodeSent = false;
            this.verificationCode = "";
            await this.sendVerificationCode();
        },
    },
};
</script>

<template>
    <div
        class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 sm:p-10 lg:p-12 border border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25 mt-12"
    >
        <!-- Заголовок секции -->
        <div class="flex items-center mb-8">
            <div
                class="w-12 h-12 bg-blue-600/90 backdrop-blur-xs rounded-2xl flex items-center justify-center mr-4 dark:bg-blue-500/90"
            >
                <svg
                    class="w-6 h-6 text-white"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.568 8.16l-1.61 7.59c-.12.54-.44.68-.89.42l-2.46-1.81-1.19 1.14c-.13.13-.24.24-.49.24l.18-2.55 4.57-4.13c.2-.18-.04-.28-.31-.1l-5.64 3.55-2.43-.76c-.53-.17-.54-.53.11-.78l9.57-3.69c.44-.16.83.1.69.78z"
                    />
                </svg>
            </div>
            <div>
                <h2
                    class="text-2xl font-jost-bold text-gray-900 dark:text-gray-100"
                >
                    Telegram
                </h2>
                <p class="text-gray-700 dark:text-gray-300">
                    Уведомления и поддержка
                </p>
            </div>
        </div>

        <!-- Загрузка данных пользователя -->
        <div
            v-if="authStore.isLoading"
            class="flex items-center justify-center py-12"
        >
            <div class="text-center">
                <div
                    class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 dark:border-blue-400 mx-auto mb-4"
                ></div>
                <p class="text-gray-700 dark:text-gray-300">
                    Загрузка данных...
                </p>
            </div>
        </div>

        <!-- Ошибка загрузки -->
        <div v-else-if="authStore.error" class="text-center py-12">
            <div
                class="bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-6 py-4 rounded-2xl dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400"
            >
                <p>{{ authStore.error }}</p>
                <button
                    @click="authStore.clearError()"
                    class="mt-3 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 underline"
                >
                    Попробовать снова
                </button>
            </div>
        </div>

        <!-- Статус: Telegram подтвержден -->
        <div
            v-else-if="userStatus === 'verified'"
            class="flex items-center justify-between"
        >
            <div class="flex items-center">
                <div
                    class="w-12 h-12 bg-green-500/90 backdrop-blur-xs rounded-2xl flex items-center justify-center mr-4 shadow-lg dark:bg-green-400/90"
                >
                    <svg
                        class="w-6 h-6 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        ></path>
                    </svg>
                </div>
                <div>
                    <p
                        class="text-xl font-jost-bold text-gray-900 dark:text-gray-100"
                    >
                        Telegram подтвержден
                    </p>
                    <p class="text-lg text-gray-700 dark:text-gray-300">
                        @{{ authStore.user?.telegram }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Статус: Нет Telegram -->
        <div v-else-if="userStatus === 'no-telegram'">
            <div
                class="bg-blue-50/80 backdrop-blur-lg border border-blue-200/30 rounded-3xl p-8 mb-8 dark:bg-gray-800/60 dark:backdrop-blur-lg dark:border-blue-800/20"
            >
                <div class="flex items-start">
                    <div
                        class="w-12 h-12 bg-blue-600/90 backdrop-blur-xs rounded-2xl flex items-center justify-center mr-6 flex-shrink-0 shadow-lg"
                    >
                        <svg
                            class="w-6 h-6 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                    </div>
                    <div>
                        <h3
                            class="text-2xl font-jost-bold text-blue-900 dark:text-gray-100 mb-4"
                        >
                            Подключите Telegram для получения уведомлений
                        </h3>
                        <p
                            class="text-lg text-blue-800 dark:text-gray-300 mb-6"
                        >
                            Получайте уведомления о статусе заказов, важных
                            обновлениях и получайте быструю поддержку через
                            нашего чат-бота в Telegram.
                        </p>
                        <ul
                            class="text-lg text-blue-800 dark:text-gray-300 space-y-3"
                        >
                            <li class="flex items-center">
                                <svg
                                    class="w-5 h-5 mr-3 flex-shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                                Уведомления о статусе заказов
                            </li>
                            <li class="flex items-center">
                                <svg
                                    class="w-5 h-5 mr-3 flex-shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                                Быстрая поддержка 24/7
                            </li>
                            <li class="flex items-center">
                                <svg
                                    class="w-5 h-5 mr-3 flex-shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                                Эксклюзивные предложения
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <button
                @click="connectTelegram"
                class="w-full bg-blue-600/90 backdrop-blur-xs hover:bg-blue-700/90 text-white px-8 py-4 rounded-2xl font-jost-bold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center dark:bg-blue-500/90 dark:hover:bg-blue-600/90"
            >
                <svg
                    class="w-6 h-6 mr-3"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.568 8.16l-1.61 7.59c-.12.54-.44.68-.89.42l-2.46-1.81-1.19 1.14c-.13.13-.24.24-.49.24l.18-2.55 4.57-4.13c.2-.18-.04-.28-.31-.1l-5.64 3.55-2.43-.76c-.53-.17-.54-.53.11-.78l9.57-3.69c.44-.16.83.1.69.78z"
                    />
                </svg>
                Подключить Telegram
            </button>
        </div>

        <!-- Статус: Telegram есть, но не подтвержден -->
        <div v-else-if="userStatus === 'unverified'">
            <div
                class="bg-pink-50/80 backdrop-blur-lg border border-pink-200/30 rounded-3xl p-8 mb-8 dark:bg-gray-800/60 dark:backdrop-blur-lg dark:border-pink-800/20"
            >
                <div class="flex items-start">
                    <div
                        class="w-12 h-12 bg-pink-600/90 backdrop-blur-xs rounded-2xl flex items-center justify-center mr-6 flex-shrink-0 shadow-lg"
                    >
                        <svg
                            class="w-6 h-6 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"
                            ></path>
                        </svg>
                    </div>
                    <div>
                        <h3
                            class="text-2xl font-jost-bold text-pink-900 dark:text-gray-100 mb-4"
                        >
                            Подтвердите свой аккаунт Telegram с помощью
                            одноразового код-пароля
                        </h3>
                        <p
                            class="text-lg text-pink-800 dark:text-gray-300 mb-6"
                        >
                            Ваш Telegram:
                            <span class="font-jost-bold"
                                >@{{ authStore.user?.telegram }}</span
                            >
                        </p>

                        <!-- Инструкции по подтверждению -->
                        <div
                            class="bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl p-6 mb-6 dark:bg-gray-800/60 dark:border-gray-700/20"
                        >
                            <h4
                                class="text-lg font-jost-bold text-gray-900 dark:text-gray-100 mb-4"
                            >
                                Инструкция по подтверждению:
                            </h4>
                            <ol
                                class="text-gray-700 dark:text-gray-300 space-y-3"
                            >
                                <li class="flex items-start">
                                    <span
                                        class="w-8 h-8 bg-pink-600/90 text-white rounded-xl flex items-center justify-center mr-4 flex-shrink-0 text-sm font-jost-bold"
                                        >1</span
                                    >
                                    <span
                                        >Зайдите в Telegram и найдите бота
                                        <span
                                            class="font-jost-bold text-pink-600 dark:text-pink-400"
                                            >@zatochkatsk_bot</span
                                        ></span
                                    >
                                </li>
                                <li class="flex items-start">
                                    <span
                                        class="w-8 h-8 bg-pink-600/90 text-white rounded-xl flex items-center justify-center mr-4 flex-shrink-0 text-sm font-jost-bold"
                                        >2</span
                                    >
                                    <span
                                        >Нажмите кнопку
                                        <span class="font-jost-bold"
                                            >"Старт"</span
                                        >
                                        в чате с ботом</span
                                    >
                                </li>
                                <li class="flex items-start">
                                    <span
                                        class="w-8 h-8 bg-pink-600/90 text-white rounded-xl flex items-center justify-center mr-4 flex-shrink-0 text-sm font-jost-bold"
                                        >3</span
                                    >
                                    <span
                                        >Вернитесь на сайт и нажмите
                                        <span class="font-jost-bold"
                                            >"Отправить код подтверждения"</span
                                        ></span
                                    >
                                </li>
                                <li class="flex items-start">
                                    <span
                                        class="w-8 h-8 bg-pink-600/90 text-white rounded-xl flex items-center justify-center mr-4 flex-shrink-0 text-sm font-jost-bold"
                                        >4</span
                                    >
                                    <span
                                        >Введите полученный код в поле ниже и
                                        нажмите
                                        <span class="font-jost-bold"
                                            >"Подтвердить"</span
                                        ></span
                                    >
                                </li>
                            </ol>
                        </div>

                        <!-- Форма подтверждения -->
                        <div v-if="!isCodeSent">
                            <button
                                @click="sendVerificationCode"
                                :disabled="isVerifying || isCheckingChat"
                                class="w-full bg-pink-600/90 backdrop-blur-xs hover:bg-pink-700/90 disabled:bg-pink-400/90 text-white px-8 py-4 rounded-2xl font-jost-bold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center dark:bg-pink-500/90 dark:hover:bg-pink-600/90"
                            >
                                <svg
                                    v-if="isCheckingChat"
                                    class="w-6 h-6 mr-3 animate-spin"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                    ></path>
                                </svg>
                                <svg
                                    v-else
                                    class="w-6 h-6 mr-3"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                                    ></path>
                                </svg>
                                {{
                                    isCheckingChat
                                        ? "Проверяем чат..."
                                        : isVerifying
                                        ? "Отправляем..."
                                        : "Отправить код подтверждения"
                                }}
                            </button>
                        </div>

                        <!-- Форма ввода кода -->
                        <div v-else class="space-y-6">
                            <div>
                                <label
                                    class="block text-lg font-jost-medium text-gray-700 dark:text-gray-300 mb-3"
                                >
                                    Введите код подтверждения:
                                </label>
                                <input
                                    v-model="verificationCode"
                                    type="text"
                                    placeholder="Введите код из Telegram"
                                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-pink-500/50 focus:border-pink-500/50 transition-all duration-300 text-lg dark:bg-gray-800/60 dark:border-gray-700/20 dark:text-gray-100 dark:placeholder-gray-400"
                                />
                            </div>

                            <div class="flex space-x-4">
                                <button
                                    @click="verifyCode"
                                    :disabled="!verificationCode || isVerifying"
                                    class="flex-1 bg-pink-600/90 backdrop-blur-xs hover:bg-pink-700/90 disabled:bg-pink-400/90 text-white px-8 py-4 rounded-2xl font-jost-bold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center dark:bg-pink-500/90 dark:hover:bg-pink-600/90"
                                >
                                    <svg
                                        class="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        ></path>
                                    </svg>
                                    {{
                                        isVerifying
                                            ? "Проверяем..."
                                            : "Подтвердить"
                                    }}
                                </button>

                                <button
                                    @click="resendCode"
                                    :disabled="isVerifying || isCheckingChat"
                                    class="flex-1 bg-white/60 backdrop-blur-xs hover:bg-white/80 text-gray-900 px-8 py-4 rounded-2xl font-jost-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-white/20 dark:bg-gray-800/60 dark:hover:bg-gray-700/80 dark:text-gray-100 dark:border-gray-700/20"
                                >
                                    <svg
                                        class="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                        ></path>
                                    </svg>
                                    Отправить снова
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
