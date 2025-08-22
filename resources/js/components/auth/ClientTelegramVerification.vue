<template>
    <div class="client-telegram-verification">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Верификация Telegram
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Подтвердите ваш Telegram аккаунт для полного доступа к системе
            </p>
        </div>

        <!-- Статус верификации -->
        <div
            v-if="verificationStatus"
            class="mb-6 p-4 rounded-lg"
            :class="statusClasses"
        >
            <div class="flex items-center">
                <i :class="statusIcon" class="mr-3"></i>
                <div>
                    <h3 class="font-medium">{{ statusTitle }}</h3>
                    <p class="text-sm">{{ statusMessage }}</p>
                </div>
            </div>
        </div>

        <!-- Форма верификации -->
        <div v-if="!verificationStatus?.is_verified" class="space-y-6">
            <!-- Информация о Telegram -->
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="mdi mdi-telegram text-blue-500 text-xl mr-3"></i>
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-white">
                            Telegram аккаунт
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ client?.telegram || "Не указан" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Кнопка отправки кода -->
            <div v-if="!codeSent">
                <button
                    @click="sendVerificationCode"
                    :disabled="loading || !client?.telegram"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                >
                    <span v-if="loading" class="flex items-center">
                        <i class="mdi mdi-loading mdi-spin mr-2"></i>
                        Отправка...
                    </span>
                    <span v-else class="flex items-center">
                        <i class="mdi mdi-telegram mr-2"></i>
                        Отправить код верификации
                    </span>
                </button>

                <p v-if="!client?.telegram" class="mt-2 text-sm text-red-600">
                    Для верификации необходимо указать Telegram аккаунт в
                    профиле
                </p>
            </div>

            <!-- Форма ввода кода -->
            <div v-if="codeSent" class="space-y-4">
                <div>
                    <label
                        for="verification_code"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Код верификации
                    </label>
                    <input
                        id="verification_code"
                        v-model="verificationCode"
                        type="text"
                        maxlength="6"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white text-center text-lg tracking-widest"
                        placeholder="000000"
                        :disabled="verifying"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Введите 6-значный код, отправленный в Telegram
                    </p>
                </div>

                <div class="flex space-x-3">
                    <button
                        @click="verifyCode"
                        :disabled="verifying || verificationCode.length !== 6"
                        class="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                    >
                        <span v-if="verifying" class="flex items-center">
                            <i class="mdi mdi-loading mdi-spin mr-2"></i>
                            Проверка...
                        </span>
                        <span v-else>Подтвердить</span>
                    </button>

                    <button
                        @click="resendCode"
                        :disabled="resending"
                        class="flex-1 flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                    >
                        <span v-if="resending" class="flex items-center">
                            <i class="mdi mdi-loading mdi-spin mr-2"></i>
                            Отправка...
                        </span>
                        <span v-else>Отправить снова</span>
                    </button>
                </div>

                <div
                    v-if="countdown > 0"
                    class="text-center text-sm text-gray-500 dark:text-gray-400"
                >
                    Повторная отправка через {{ countdown }} сек
                </div>
            </div>
        </div>

        <!-- Успешная верификация -->
        <div v-else class="text-center">
            <div class="mb-4">
                <i class="mdi mdi-check-circle text-green-500 text-6xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                Telegram верифицирован!
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Ваш аккаунт полностью подтвержден. Теперь вы можете использовать
                все функции системы.
            </p>
            <button
                @click="$emit('verification-complete')"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-accent hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-colors duration-200"
            >
                Продолжить
            </button>
        </div>
    </div>
</template>

<script>
import clientAuthService from "../../services/clientAuthService.js";

export default {
    name: "ClientTelegramVerification",
    props: {
        client: {
            type: Object,
            required: true,
        },
    },
    emits: ["verification-complete"],
    data() {
        return {
            verificationStatus: null,
            codeSent: false,
            verificationCode: "",
            loading: false,
            verifying: false,
            resending: false,
            countdown: 0,
            countdownInterval: null,
        };
    },
    computed: {
        statusClasses() {
            if (!this.verificationStatus) return "";

            if (this.verificationStatus.is_verified) {
                return "bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800";
            }

            return "bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800";
        },
        statusIcon() {
            if (!this.verificationStatus) return "";

            if (this.verificationStatus.is_verified) {
                return "mdi mdi-check-circle text-green-500";
            }

            return "mdi mdi-alert-circle text-yellow-500";
        },
        statusTitle() {
            if (!this.verificationStatus) return "";

            if (this.verificationStatus.is_verified) {
                return "Telegram верифицирован";
            }

            return "Требуется верификация";
        },
        statusMessage() {
            if (!this.verificationStatus) return "";

            if (this.verificationStatus.is_verified) {
                return "Ваш аккаунт полностью подтвержден";
            }

            return "Подтвердите Telegram для полного доступа к системе";
        },
    },
    async mounted() {
        await this.checkVerificationStatus();
    },
    beforeUnmount() {
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }
    },
    methods: {
        async checkVerificationStatus() {
            try {
                const response = await clientAuthService.getTelegramStatus(
                    this.client.phone
                );
                this.verificationStatus = response.data;
            } catch (error) {
                console.error("Error checking verification status:", error);
            }
        },

        async sendVerificationCode() {
            this.loading = true;

            try {
                await clientAuthService.sendTelegramCode(this.client.phone);
                this.codeSent = true;
                this.startCountdown();

                if (window.modalService) {
                    window.modalService.alert(
                        "Код отправлен",
                        "Код верификации отправлен в ваш Telegram",
                        "info"
                    );
                }
            } catch (error) {
                console.error("Error sending verification code:", error);

                if (window.modalService) {
                    window.modalService.alert(
                        "Ошибка",
                        error.message || "Не удалось отправить код",
                        "error"
                    );
                }
            } finally {
                this.loading = false;
            }
        },

        async verifyCode() {
            if (this.verificationCode.length !== 6) return;

            this.verifying = true;

            try {
                await clientAuthService.verifyTelegramCode(
                    this.client.phone,
                    this.verificationCode
                );

                // Обновляем статус
                await this.checkVerificationStatus();

                if (window.modalService) {
                    window.modalService.alert(
                        "Успех",
                        "Telegram успешно верифицирован!",
                        "success"
                    );
                }

                this.$emit("verification-complete");
            } catch (error) {
                console.error("Error verifying code:", error);

                if (window.modalService) {
                    window.modalService.alert(
                        "Ошибка",
                        error.message || "Неверный код верификации",
                        "error"
                    );
                }
            } finally {
                this.verifying = false;
            }
        },

        async resendCode() {
            this.resending = true;

            try {
                await clientAuthService.sendTelegramCode(this.client.phone);
                this.startCountdown();

                if (window.modalService) {
                    window.modalService.alert(
                        "Код отправлен",
                        "Новый код верификации отправлен",
                        "info"
                    );
                }
            } catch (error) {
                console.error("Error resending code:", error);

                if (window.modalService) {
                    window.modalService.alert(
                        "Ошибка",
                        error.message || "Не удалось отправить код",
                        "error"
                    );
                }
            } finally {
                this.resending = false;
            }
        },

        startCountdown() {
            this.countdown = 60;

            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
            }

            this.countdownInterval = setInterval(() => {
                this.countdown--;

                if (this.countdown <= 0) {
                    clearInterval(this.countdownInterval);
                    this.countdownInterval = null;
                }
            }, 1000);
        },
    },
};
</script>
