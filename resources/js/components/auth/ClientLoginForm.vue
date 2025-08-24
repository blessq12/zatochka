<template>
    <div class="hero-card rounded-2xl shadow-lg p-8" ref="formContainer">
        <form @submit.prevent="handleSubmit" class="space-y-6" ref="form">
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
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >Номер телефона</label
                >
                <div class="relative">
                    <input
                        type="tel"
                        class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                        v-model="form.phone"
                        v-maska
                        data-maska="+7 (###) ###-##-##"
                        placeholder="+7 (___) ___-__-__"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                errors.phone,
                        }"
                        @focus="handleFieldFocus"
                        @blur="handleFieldBlur"
                        required
                    />
                    <i
                        class="mdi mdi-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                    ></i>
                </div>
                <span
                    v-if="errors.phone"
                    class="text-red-500 text-sm font-medium"
                    ref="errorPhone"
                    >{{ errors.phone }}</span
                >
            </div>

            <!-- Пароль -->
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >Пароль</label
                >
                <div class="relative">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        class="w-full px-4 py-3 pl-12 pr-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                        v-model="form.password"
                        placeholder="Введите пароль"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                errors.password,
                        }"
                        @focus="handleFieldFocus"
                        @blur="handleFieldBlur"
                        required
                    />
                    <i
                        class="mdi mdi-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                    ></i>
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    >
                        <i
                            :class="
                                showPassword ? 'mdi mdi-eye-off' : 'mdi mdi-eye'
                            "
                        ></i>
                    </button>
                </div>
                <span
                    v-if="errors.password"
                    class="text-red-500 text-sm font-medium"
                    ref="errorPassword"
                    >{{ errors.password }}</span
                >
            </div>

            <!-- Запомнить меня -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        class="w-5 h-5 text-accent accent-accent"
                        v-model="form.remember"
                    />
                    <label class="dark:text-white text-sm"
                        >Запомнить меня</label
                    >
                </div>
                <button
                    type="button"
                    @click="$emit('forgot-password')"
                    class="text-sm text-accent hover:text-accent/80 dark:text-accent-light dark:hover:text-accent-light/80 font-medium"
                    :disabled="loading"
                >
                    Забыли пароль?
                </button>
            </div>

            <!-- Кнопка отправки -->
            <button
                type="submit"
                class="w-full bg-gradient-to-r from-accent to-pink-600 text-white font-semibold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center"
                :disabled="loading"
                ref="submitButton"
            >
                <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                <i v-else class="mdi mdi-login mr-2"></i>
                {{ loading ? "Входим..." : "Войти" }}
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
                        Успешный вход!
                    </p>
                    <p class="text-green-700 dark:text-green-300">
                        Добро пожаловать в систему
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
import { useAuthStore } from "../../stores/auth.js";
import { loginFormSchema, validateForm } from "../../validation/schemas.js";

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
            success: false,
            showPassword: false,
        };
    },
    computed: {
        authStore() {
            return useAuthStore();
        },
        loading() {
            return this.authStore.getLoading;
        },
        error() {
            return this.authStore.getError;
        },
    },
    mounted() {
        // Анимация появления формы
        this.$nextTick(() => {
            this.animateFormEnter();
        });
    },
    methods: {
        // Анимация появления формы
        animateFormEnter() {
            if (!this.$refs.formContainer) return;

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

        // Анимация кнопки загрузки
        animateButtonLoading() {
            if (!this.$refs.submitButton) return;

            gsap.to(this.$refs.submitButton, {
                scale: 0.95,
                duration: 0.2,
                ease: "power2.out",
            });
        },

        // Анимация сброса кнопки
        animateButtonReset() {
            if (!this.$refs.submitButton) return;

            gsap.to(this.$refs.submitButton, {
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
            if (!this.$refs.errorMessage) return;

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

        async handleSubmit() {
            // Валидация при сабмите
            const result = await validateForm(loginFormSchema, this.form);

            if (!result.isValid) {
                this.errors = result.errors;

                // Анимация всех полей с ошибками
                Object.keys(this.errors).forEach((fieldName, index) => {
                    const field = this.$el.querySelector(
                        `[v-model="form.${fieldName}"]`
                    );
                    if (field) {
                        // Задержка для последовательной анимации
                        gsap.delayedCall(index * 0.1, () => {
                            this.animateFieldError(field);
                            this.highlightErrorField(field);
                        });
                    }
                });

                // Анимация текстов ошибок
                this.$nextTick(() => {
                    Object.keys(this.errors).forEach((fieldName, index) => {
                        const errorElement =
                            this.$refs[
                                `error${
                                    fieldName.charAt(0).toUpperCase() +
                                    fieldName.slice(1)
                                }`
                            ];
                        if (errorElement) {
                            gsap.delayedCall(index * 0.1 + 0.3, () => {
                                this.showErrorText(errorElement);
                            });
                        }
                    });
                });

                return;
            }

            this.success = false;

            // Анимация кнопки загрузки
            this.animateButtonLoading();

            try {
                const response = await this.authStore.login(this.form);

                this.success = true;
                this.$emit("login-success", response);

                // Анимация успешного сообщения
                this.$nextTick(() => {
                    this.animateSuccess();
                });
            } catch (error) {
                console.error("Login error:", error);

                // Ошибки уже обработаны в сторе
                console.error("Login form error:", error);

                // Анимация ошибки
                this.$nextTick(() => {
                    this.animateError();
                });
            } finally {
                this.animateButtonReset();
            }
        },
    },
};
</script>

<style scoped>
/* Только базовые стили для анимаций */
input,
select,
textarea {
    transition: all 0.3s ease;
}

/* Анимация для чекбокса */
input[type="checkbox"]:checked {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}
</style>
