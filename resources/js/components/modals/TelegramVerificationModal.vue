<template>
    <Modal
        :show="show"
        title="Верификация Telegram"
        size="lg"
        @close="handleClose"
    >
        <div class="space-y-6">
            <!-- Статус верификации -->
            <div
                :class="[
                    'p-4 rounded-lg border-2 transition-all duration-300',
                    statusClasses,
                ]"
            >
                <div class="flex items-center">
                    <i :class="[statusIcon, 'text-2xl mr-3']"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            {{ statusTitle }}
                        </h3>
                        <p
                            class="text-sm text-gray-600 dark:text-gray-400 mt-1"
                        >
                            {{ statusMessage }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Форма верификации -->
            <div
                v-if="!success && !verificationStatus?.is_verified"
                class="space-y-4"
            >
                <!-- Кнопка отправки кода -->
                <div v-if="!codeSent" class="text-center">
                    <button
                        @click="sendVerificationCode"
                        :disabled="sendingCode || !client?.telegram"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                        ref="sendCodeButton"
                    >
                        <i
                            v-if="sendingCode"
                            class="mdi mdi-loading mdi-spin mr-2"
                        ></i>
                        <i v-else class="mdi mdi-telegram mr-2"></i>
                        {{ sendingCode ? "Отправляем..." : "Отправить код" }}
                    </button>

                    <!-- Сообщение если Telegram не указан -->
                    <div
                        v-if="!client?.telegram"
                        class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg"
                    >
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            Укажите Telegram аккаунт в профиле
                        </p>
                    </div>
                </div>

                <!-- Форма ввода кода -->
                <div v-if="codeSent" class="space-y-4">
                    <div class="text-center">
                        <p
                            class="text-sm text-gray-600 dark:text-gray-400 mb-4"
                        >
                            Введите код из Telegram:
                        </p>
                    </div>

                    <div class="space-y-2">
                        <div class="relative">
                            <input
                                type="text"
                                class="w-full px-4 py-3 pl-12 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all duration-200"
                                v-model="verificationCode"
                                placeholder="Код верификации"
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
                                class="mdi mdi-key absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"
                            ></i>
                        </div>
                        <span
                            v-if="errors.verificationCode"
                            class="text-red-500 text-sm"
                            ref="errorVerificationCode"
                            >{{ errors.verificationCode }}</span
                        >
                    </div>

                    <div class="text-center">
                        <button
                            @click="verifyCode"
                            :disabled="verifying || !verificationCode"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                            ref="verifyButton"
                        >
                            <i
                                v-if="verifying"
                                class="mdi mdi-loading mdi-spin mr-2"
                            ></i>
                            <i v-else class="mdi mdi-check mr-2"></i>
                            {{ verifying ? "Проверяем..." : "Подтвердить" }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Успешное сообщение -->
            <div
                v-if="success"
                class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg"
                ref="successMessage"
            >
                <div class="flex items-center">
                    <i
                        class="mdi mdi-check-circle text-green-500 text-xl mr-3"
                    ></i>
                    <div>
                        <p
                            class="font-medium text-green-800 dark:text-green-200"
                        >
                            Верификация успешна!
                        </p>
                        <p
                            class="text-sm text-green-700 dark:text-green-300 mt-1"
                        >
                            Ваш Telegram аккаунт подтвержден
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ошибка -->
            <div
                v-if="localError"
                class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
                ref="errorMessage"
            >
                <div class="flex items-center">
                    <i
                        class="mdi mdi-alert-circle text-red-500 text-xl mr-3"
                    ></i>
                    <div>
                        <p class="text-red-800 dark:text-red-200">
                            {{ localError }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer с кнопками -->
        <template #footer>
            <div class="flex justify-end space-x-3">
                <button
                    v-if="!success && !verificationStatus?.is_verified"
                    @click="handleClose"
                    class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                >
                    Отмена
                </button>
                <button
                    v-if="success || verificationStatus?.is_verified"
                    @click="handleClose"
                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
                >
                    Закрыть
                </button>
            </div>
        </template>
    </Modal>
</template>

<script>
import { gsap } from "gsap";
import { useAuthStore } from "../../stores/auth.js";

export default {
    name: "TelegramVerificationModal",
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        client: {
            type: Object,
            required: false,
            default: null,
        },
    },
    emits: ["close", "verification-complete"],
    data() {
        return {
            verificationStatus: null,
            codeSent: false,
            verificationCode: "",
            verifying: false,
            sendingCode: false,
            success: false,
            localError: null,
            errors: {},
            authStore: null,
        };
    },
    computed: {
        statusClasses() {
            if (!this.verificationStatus)
                return "bg-gray-50 dark:bg-gray-900/20 border-gray-200 dark:border-gray-800";

            if (this.verificationStatus.is_verified) {
                return "bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800";
            }

            return "bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800";
        },
        statusIcon() {
            if (!this.verificationStatus)
                return "mdi mdi-loading mdi-spin text-gray-500";

            if (this.verificationStatus.is_verified) {
                return "mdi mdi-check-circle text-green-500";
            }

            return "mdi mdi-alert-circle text-yellow-500";
        },
        statusTitle() {
            if (!this.verificationStatus) return "Проверка статуса...";

            if (this.verificationStatus.is_verified) {
                return "Telegram верифицирован";
            }

            return "Требуется верификация";
        },
        statusMessage() {
            if (!this.verificationStatus)
                return "Загружаем статус верификации...";

            if (this.verificationStatus.is_verified) {
                return "Ваш аккаунт полностью подтвержден";
            }

            return "Подтвердите Telegram для полного доступа к системе";
        },
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.initModal();
            }
        },
    },
    methods: {
        async initModal() {
            this.authStore = useAuthStore();
            await this.checkVerificationStatus();
        },

        handleClose() {
            this.$emit("close");
        },

        // Анимация ошибки поля - тряска
        animateFieldError(field) {
            if (!field) return;

            gsap.to(field, {
                x: [-8, 8, -8, 8, -4, 4, 0],
                duration: 0.6,
                ease: "power2.out",
            });
        },

        // Анимация подсветки поля с ошибкой
        highlightErrorField(field) {
            if (!field) return;

            gsap.to(field, {
                borderColor: "#ef4444",
                boxShadow: "0 0 0 3px rgba(239, 68, 68, 0.2)",
                duration: 0.3,
                ease: "power2.out",
            });
        },

        // Анимация появления текста ошибки
        showErrorText(errorElement) {
            if (!errorElement) return;

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
            if (!field) return;

            gsap.to(field, {
                scale: 1.02,
                duration: 0.2,
                ease: "power2.out",
            });
        },

        // Анимация потери фокуса
        animateFieldBlur(field) {
            if (!field) return;

            gsap.to(field, {
                scale: 1,
                duration: 0.2,
                ease: "power2.out",
            });
        },

        // Анимация успешного сообщения
        animateSuccess() {
            if (!this.$refs.successMessage) return;

            gsap.fromTo(
                this.$refs.successMessage,
                {
                    opacity: 0,
                    scale: 0.8,
                    y: -20,
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
            if (!this.$refs.errorMessage) return;

            gsap.fromTo(
                this.$refs.errorMessage,
                {
                    opacity: 0,
                    scale: 0.8,
                    y: -20,
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

        handleFieldFocus(event) {
            this.animateFieldFocus(event.target);
        },

        handleFieldBlur(event) {
            this.animateFieldBlur(event.target);
        },

        async checkVerificationStatus() {
            try {
                const response = await this.authStore.checkVerificationStatus();
                this.verificationStatus = response.data;
            } catch (error) {
                console.error("Ошибка проверки статуса:", error);
                this.localError = "Ошибка проверки статуса верификации";
            }
        },

        async sendVerificationCode() {
            this.sendingCode = true;
            this.localError = null;
            this.errors = {};

            try {
                await this.authStore.sendVerificationCode();
                this.codeSent = true;

                // Фокус на поле ввода кода
                this.$nextTick(() => {
                    if (this.$refs.codeInput) {
                        this.$refs.codeInput.focus();
                    }
                });
            } catch (error) {
                this.localError = error.message || "Ошибка отправки кода";
                this.animateError();
            } finally {
                this.sendingCode = false;
            }
        },

        async verifyCode() {
            if (!this.verificationCode.trim()) {
                this.errors.verificationCode = "Введите код верификации";
                this.animateFieldError(this.$refs.codeInput);
                this.highlightErrorField(this.$refs.codeInput);
                this.showErrorText(this.$refs.errorVerificationCode);
                return;
            }

            this.verifying = true;
            this.localError = null;
            this.errors = {};

            try {
                const response = await this.authStore.verifyCode(
                    this.verificationCode
                );
                this.verificationStatus = response.data;
                this.success = true;

                // Анимация успеха
                this.$nextTick(() => {
                    this.animateSuccess();
                });

                // Эмитим событие о завершении верификации
                this.$emit("verification-complete", response.data);
            } catch (error) {
                this.localError = error.message || "Ошибка верификации кода";
                this.animateError();
            } finally {
                this.verifying = false;
            }
        },
    },
};
</script>
