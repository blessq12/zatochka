<template>
    <div class="hero-card rounded-2xl shadow-lg p-8" ref="formContainer">
        <!-- Статус верификации -->
        <div
            :class="['p-4 rounded-xl border-l-4 mb-6', statusClasses]"
            ref="statusCard"
        >
            <div class="flex items-center">
                <i :class="[statusIcon, 'text-2xl mr-3']"></i>
                <div>
                    <h3 class="font-semibold text-lg">{{ statusTitle }}</h3>
                    <p class="text-sm mt-1">{{ statusMessage }}</p>
                </div>
            </div>
        </div>

        <!-- Форма верификации -->
        <div v-if="!verificationStatus?.is_verified" class="space-y-6">
            <!-- Информация о Telegram -->
            <div
                class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700"
                ref="telegramInfo"
            >
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
            <div v-if="!codeSent" class="text-center">
                <button
                    @click="sendVerificationCode"
                    :disabled="loading || !client?.telegram"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center"
                    ref="sendCodeButton"
                >
                    <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                    <i v-else class="mdi mdi-telegram mr-2"></i>
                    {{
                        loading
                            ? "Отправляем..."
                            : !client?.telegram
                            ? "Telegram не указан"
                            : "Отправить код верификации"
                    }}
                </button>

                <!-- Сообщение если Telegram не указан -->
                <div
                    v-if="!client?.telegram"
                    class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg"
                >
                    <div class="flex items-center">
                        <i
                            class="mdi mdi-alert-circle text-yellow-600 dark:text-yellow-400 text-lg mr-2"
                        ></i>
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            Для верификации необходимо указать Telegram аккаунт
                            в профиле
                        </p>
                    </div>
                </div>
            </div>

            <!-- Форма ввода кода -->
            <div v-if="codeSent" class="space-y-4" ref="codeForm">
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Код отправлен в Telegram. Введите его ниже:
                    </p>
                </div>

                <div class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                        >Код верификации</label
                    >
                    <div class="relative">
                        <input
                            type="text"
                            class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                            v-model="verificationCode"
                            placeholder="Введите код"
                            maxlength="6"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                    errors.verificationCode,
                            }"
                            @focus="handleFieldFocus"
                            @blur="handleFieldBlur"
                            ref="codeInput"
                        />
                        <i
                            class="mdi mdi-key absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                        ></i>
                    </div>
                    <span
                        v-if="errors.verificationCode"
                        class="text-red-500 text-sm font-medium"
                        ref="errorVerificationCode"
                        >{{ errors.verificationCode }}</span
                    >
                </div>

                <div class="flex space-x-3">
                    <button
                        @click="verifyCode"
                        :disabled="verifying || !verificationCode"
                        class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 px-4 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center"
                        ref="verifyButton"
                    >
                        <i
                            v-if="verifying"
                            class="mdi mdi-loading mdi-spin mr-2"
                        ></i>
                        <i v-else class="mdi mdi-check mr-2"></i>
                        {{ verifying ? "Проверяем..." : "Подтвердить" }}
                    </button>

                    <button
                        @click="resendCode"
                        :disabled="resending || countdown > 0"
                        class="px-4 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                        ref="resendButton"
                    >
                        <i
                            v-if="resending"
                            class="mdi mdi-loading mdi-spin mr-2"
                        ></i>
                        <i v-else class="mdi mdi-refresh mr-2"></i>
                        {{
                            countdown > 0 ? `${countdown}с` : "Отправить снова"
                        }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Успешное сообщение -->
        <div
            v-if="success"
            class="mt-6 p-4 bg-green-100 dark:bg-green-900/20 border border-green-500 rounded-lg"
            ref="successMessage"
        >
            <div class="flex items-center">
                <i
                    class="mdi mdi-check-circle text-green-600 dark:text-green-400 text-2xl mr-3"
                ></i>
                <div>
                    <p class="font-bold text-green-800 dark:text-green-200">
                        Верификация успешна!
                    </p>
                    <p class="text-green-700 dark:text-green-300">
                        Ваш аккаунт полностью подтвержден
                    </p>
                </div>
            </div>
        </div>

        <!-- Ошибка -->
        <div
            v-if="error"
            class="mt-6 p-4 bg-red-100 dark:bg-red-900/20 border border-red-500 rounded-lg"
            ref="errorMessage"
        >
            <div class="flex items-center">
                <i
                    class="mdi mdi-alert-circle text-red-600 dark:text-red-400 text-2xl mr-3"
                ></i>
                <div>
                    <p class="font-bold text-red-800 dark:text-red-200">
                        Ошибка!
                    </p>
                    <p class="text-red-700 dark:text-red-300">{{ error }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { gsap } from "gsap";
import clientAuthService from "../../services/clientAuthService.js";

export default {
    name: "ClientTelegramVerification",
    props: {
        client: {
            type: Object,
            required: false,
            default: null,
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
            success: false,
            error: null,
            errors: {},
        };
    },
    computed: {
        statusClasses() {
            if (!this.verificationStatus) return "";

            if (this.verificationStatus.is_verified) {
                return "bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800";
            }

            return "bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800";
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
    watch: {
        // Следим за изменениями клиента
        client: {
            handler(newClient) {
                if (newClient && !this.verificationStatus) {
                    this.checkVerificationStatus();
                }
            },
            immediate: true,
        },
    },
    async mounted() {
        // Анимация появления компонента
        this.$nextTick(() => {
            this.animateComponentEnter();
        });
    },
    beforeUnmount() {
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }
    },
    methods: {
        // Анимация появления компонента
        animateComponentEnter() {
            gsap.fromTo(
                this.$refs.formContainer,
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

        // Анимация ошибки поля - тряска
        animateFieldError(field) {
            gsap.to(field, {
                x: [-8, 8, -8, 8, -4, 4, 0],
                duration: 0.6,
                ease: "power2.out",
            });
        },

        // Анимация подсветки поля с ошибкой
        highlightErrorField(field) {
            gsap.to(field, {
                borderColor: "#ef4444",
                boxShadow: "0 0 0 3px rgba(239, 68, 68, 0.2)",
                duration: 0.3,
                ease: "power2.out",
            });
        },

        // Анимация появления текста ошибки
        showErrorText(errorElement) {
            gsap.fromTo(
                errorElement,
                {
                    opacity: 0,
                    scale: 0.8,
                    y: -10,
                },
                {
                    opacity: 1,
                    scale: 1,
                    y: 0,
                    duration: 0.4,
                    ease: "back.out(1.7)",
                }
            );
        },

        // Анимация фокуса на поле
        animateFieldFocus(field) {
            gsap.to(field, {
                scale: 1.02,
                duration: 0.2,
                ease: "power2.out",
            });
        },

        // Анимация потери фокуса
        animateFieldBlur(field) {
            gsap.to(field, {
                scale: 1,
                duration: 0.2,
                ease: "power2.out",
            });
        },

        // Анимация успешного сообщения
        animateSuccess() {
            gsap.fromTo(
                this.$refs.successMessage,
                {
                    opacity: 0,
                    scale: 0.8,
                    y: 20,
                },
                {
                    opacity: 1,
                    scale: 1,
                    y: 0,
                    duration: 0.5,
                    ease: "back.out(1.7)",
                }
            );
        },

        // Анимация ошибки
        animateError() {
            gsap.fromTo(
                this.$refs.errorMessage,
                {
                    opacity: 0,
                    y: -30,
                    scale: 0.9,
                },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.5,
                    ease: "back.out(1.7)",
                }
            );
        },

        handleFieldFocus(event) {
            this.animateFieldFocus(event.target);
        },

        handleFieldBlur(event) {
            this.animateFieldBlur(event.target);
        },

        async checkVerificationStatus() {
            try {
                const response =
                    await clientAuthService.checkVerificationStatus();
                this.verificationStatus = response.data;
            } catch (error) {
                console.error("Verification status check error:", error);

                // Если ошибка авторизации, не показываем ошибку пользователю
                if (error.message === "Пользователь не авторизован") {
                    this.verificationStatus = null;
                    return;
                }

                // Для других ошибок показываем сообщение
                this.error = error.message || "Ошибка проверки статуса";
                this.$nextTick(() => {
                    this.animateError();
                });
            }
        },

        async sendVerificationCode() {
            // Проверяем, есть ли у клиента Telegram
            if (!this.client?.telegram) {
                this.error =
                    "Telegram аккаунт не указан. Укажите его в профиле.";
                this.$nextTick(() => {
                    this.animateError();
                });
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                await clientAuthService.sendVerificationCode();
                this.codeSent = true;
                this.startCountdown();

                // Анимация появления формы кода
                this.$nextTick(() => {
                    gsap.fromTo(
                        this.$refs.codeForm,
                        {
                            opacity: 0,
                            y: 20,
                            scale: 0.95,
                        },
                        {
                            opacity: 1,
                            y: 0,
                            scale: 1,
                            duration: 0.5,
                            ease: "back.out(1.7)",
                        }
                    );
                });
            } catch (error) {
                // Если ошибка авторизации, не показываем ошибку пользователю
                if (error.message === "Пользователь не авторизован") {
                    this.error = "Сессия истекла. Войдите в систему заново.";
                } else {
                    this.error = error.message || "Ошибка отправки кода";
                }

                this.$nextTick(() => {
                    this.animateError();
                });
            } finally {
                this.loading = false;
            }
        },

        async verifyCode() {
            if (!this.verificationCode) {
                this.errors.verificationCode = "Введите код верификации";
                this.$nextTick(() => {
                    this.animateFieldError(this.$refs.codeInput);
                    this.highlightErrorField(this.$refs.codeInput);
                    this.showErrorText(this.$refs.errorVerificationCode);
                });
                return;
            }

            this.verifying = true;
            this.error = null;
            this.errors = {};

            try {
                const response = await clientAuthService.verifyCode(
                    this.verificationCode
                );
                this.success = true;
                this.verificationStatus = { is_verified: true };

                // Обновляем данные клиента если они пришли в ответе
                if (response.data?.client) {
                    this.$emit("verification-complete", response.data.client);
                } else {
                    this.$emit("verification-complete");
                }

                // Анимация успешного сообщения
                this.$nextTick(() => {
                    this.animateSuccess();
                });
            } catch (error) {
                // Если ошибка авторизации, не показываем ошибку пользователю
                if (error.message === "Пользователь не авторизован") {
                    this.error = "Сессия истекла. Войдите в систему заново.";
                } else {
                    this.error = error.message || "Неверный код верификации";
                }

                this.$nextTick(() => {
                    this.animateError();
                });
            } finally {
                this.verifying = false;
            }
        },

        async resendCode() {
            this.resending = true;
            this.error = null;

            try {
                await clientAuthService.sendVerificationCode();
                this.startCountdown();
            } catch (error) {
                this.error = error.message || "Ошибка отправки кода";
                this.$nextTick(() => {
                    this.animateError();
                });
            } finally {
                this.resending = false;
            }
        },

        startCountdown() {
            this.countdown = 60;
            this.countdownInterval = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    clearInterval(this.countdownInterval);
                }
            }, 1000);
        },
    },
};
</script>

<style scoped>
/* Только базовые стили для анимаций */
input {
    transition: all 0.3s ease;
}
</style>
