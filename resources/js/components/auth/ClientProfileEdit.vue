<template>
    <div class="client-profile-edit" ref="container">
        <!-- Заголовок -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Редактирование профиля
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                Обновите ваши личные данные
            </p>
        </div>

        <!-- Форма редактирования -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- ФИО -->
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                >
                    ФИО
                </label>
                <div class="relative">
                    <input
                        type="text"
                        v-model="formData.full_name"
                        class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                        placeholder="Введите ваше полное имя"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                errors.full_name,
                        }"
                        @focus="handleFieldFocus"
                        @blur="handleFieldBlur"
                        ref="fullNameInput"
                    />
                    <i
                        class="mdi mdi-account absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                    ></i>
                </div>
                <span
                    v-if="errors.full_name"
                    class="text-red-500 text-sm font-medium"
                    ref="errorFullName"
                    >{{ errors.full_name }}</span
                >
            </div>

            <!-- Телефон -->
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                >
                    Телефон
                </label>
                <div class="relative">
                    <input
                        type="tel"
                        v-model="formData.phone"
                        class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                        placeholder="+7 (___) ___-__-__"
                        v-maska
                        data-maska="+7 (###) ###-##-##"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                errors.phone,
                        }"
                        @focus="handleFieldFocus"
                        @blur="handleFieldBlur"
                        ref="phoneInput"
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

            <!-- Telegram -->
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                >
                    Telegram
                </label>
                <div class="relative">
                    <input
                        type="text"
                        v-model="formData.telegram"
                        class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                        placeholder="@username"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                errors.telegram,
                        }"
                        @focus="handleFieldFocus"
                        @blur="handleFieldBlur"
                        ref="telegramInput"
                    />
                    <i
                        class="mdi mdi-telegram absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                    ></i>
                </div>
                <span
                    v-if="errors.telegram"
                    class="text-red-500 text-sm font-medium"
                    ref="errorTelegram"
                    >{{ errors.telegram }}</span
                >
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Укажите username без @ (например: username)
                </p>
            </div>

            <!-- Дата рождения -->
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                >
                    Дата рождения
                </label>
                <div class="relative">
                    <input
                        type="date"
                        v-model="formData.birth_date"
                        class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                errors.birth_date,
                        }"
                        @focus="handleFieldFocus"
                        @blur="handleFieldBlur"
                        ref="birthDateInput"
                    />
                    <i
                        class="mdi mdi-cake absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                    ></i>
                </div>
                <span
                    v-if="errors.birth_date"
                    class="text-red-500 text-sm font-medium"
                    ref="errorBirthDate"
                    >{{ errors.birth_date }}</span
                >
            </div>

            <!-- Адрес доставки -->
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                >
                    Адрес доставки
                </label>
                <div class="relative">
                    <textarea
                        v-model="formData.delivery_address"
                        rows="3"
                        class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300 resize-none"
                        placeholder="Укажите адрес для доставки"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                errors.delivery_address,
                        }"
                        @focus="handleFieldFocus"
                        @blur="handleFieldBlur"
                        ref="deliveryAddressInput"
                    ></textarea>
                    <i
                        class="mdi mdi-map-marker absolute left-3 top-3 text-accent text-lg"
                    ></i>
                </div>
                <span
                    v-if="errors.delivery_address"
                    class="text-red-500 text-sm font-medium"
                    ref="errorDeliveryAddress"
                    >{{ errors.delivery_address }}</span
                >
            </div>

            <!-- Кнопки -->
            <div class="flex space-x-3 pt-4">
                <button
                    type="button"
                    @click="handleCancel"
                    class="flex-1 flex justify-center items-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all duration-200 transform hover:scale-105"
                >
                    <i class="mdi mdi-close mr-2"></i>
                    Отмена
                </button>
                <button
                    type="submit"
                    :disabled="loading"
                    class="flex-1 flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-gradient-to-r from-accent to-pink-600 hover:from-accent/90 hover:to-pink-600/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all duration-200 transform hover:scale-105 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none"
                    ref="submitButton"
                >
                    <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                    <i v-else class="mdi mdi-content-save mr-2"></i>
                    {{ loading ? "Сохранение..." : "Сохранить" }}
                </button>
            </div>
        </form>

        <!-- Сообщения об ошибках и успехе -->
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
                        Успешно!
                    </p>
                    <p class="text-green-700 dark:text-green-300">
                        Профиль обновлен
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { gsap } from "gsap";
import { useAuthStore } from "../../stores/auth.js";
import { profileEditSchema, validateForm } from "../../validation/schemas.js";

export default {
    name: "ClientProfileEdit",
    props: {
        client: {
            type: Object,
            required: true,
        },
    },
    emits: ["profile-updated", "cancel"],
    data() {
        return {
            formData: {
                full_name: "",
                phone: "",
                telegram: "",
                birth_date: "",
                delivery_address: "",
            },
            success: false,
            errors: {},
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
        this.initializeForm();
        this.animateComponentEnter();
    },
    methods: {
        // Анимация появления компонента
        animateComponentEnter() {
            if (!this.$refs.container) return;

            gsap.fromTo(
                this.$refs.container,
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

        // Инициализация формы данными клиента
        initializeForm() {
            this.formData = {
                full_name: this.client.full_name || "",
                phone: this.client.phone || "",
                telegram: this.client.telegram
                    ? this.client.telegram.startsWith("@")
                        ? this.client.telegram
                        : `@${this.client.telegram}`
                    : "",
                birth_date: this.client.birth_date || "",
                delivery_address: this.client.delivery_address || "",
            };
        },

        // Обработка отправки формы
        async handleSubmit() {
            this.errors = {};

            try {
                // Подготавливаем данные для отправки
                const submitData = { ...this.formData };

                // Убираем @ из Telegram username если есть
                if (
                    submitData.telegram &&
                    submitData.telegram.startsWith("@")
                ) {
                    submitData.telegram = submitData.telegram.substring(1);
                }

                // Валидация формы
                const validationResult = await validateForm(
                    profileEditSchema,
                    submitData
                );

                if (!validationResult.isValid) {
                    this.errors = validationResult.errors;
                    this.animateValidationErrors();
                    return;
                }

                // Отправка данных на сервер
                const response = await this.authStore.updateProfile(submitData);

                this.success = true;
                this.$emit("profile-updated", response.data.client);

                // Анимация успешного сообщения
                this.$nextTick(() => {
                    this.animateSuccess();
                });

                // Автоматическое закрытие через 2 секунды
                setTimeout(() => {
                    this.$emit("cancel");
                }, 2000);
            } catch (error) {
                this.error = error.message || "Ошибка обновления профиля";
                this.$nextTick(() => {
                    this.animateError();
                });
            } finally {
                this.loading = false;
            }
        },

        // Анимация ошибок валидации
        animateValidationErrors() {
            this.$nextTick(() => {
                // Анимация для каждого поля с ошибкой
                if (this.errors.full_name && this.$refs.fullNameInput) {
                    this.animateFieldError(this.$refs.fullNameInput);
                    this.highlightErrorField(this.$refs.fullNameInput);
                    this.showErrorText(this.$refs.errorFullName);
                }
                if (this.errors.phone && this.$refs.phoneInput) {
                    this.animateFieldError(this.$refs.phoneInput);
                    this.highlightErrorField(this.$refs.phoneInput);
                    this.showErrorText(this.$refs.errorPhone);
                }
                if (this.errors.telegram && this.$refs.telegramInput) {
                    this.animateFieldError(this.$refs.telegramInput);
                    this.highlightErrorField(this.$refs.telegramInput);
                    this.showErrorText(this.$refs.errorTelegram);
                }
                if (this.errors.birth_date && this.$refs.birthDateInput) {
                    this.animateFieldError(this.$refs.birthDateInput);
                    this.highlightErrorField(this.$refs.birthDateInput);
                    this.showErrorText(this.$refs.errorBirthDate);
                }
                if (
                    this.errors.delivery_address &&
                    this.$refs.deliveryAddressInput
                ) {
                    this.animateFieldError(this.$refs.deliveryAddressInput);
                    this.highlightErrorField(this.$refs.deliveryAddressInput);
                    this.showErrorText(this.$refs.errorDeliveryAddress);
                }
            });
        },

        // Обработка отмены
        handleCancel() {
            this.$emit("cancel");
        },
    },
};
</script>

<style scoped>
/* Дополнительные стили для анимаций */
.client-profile-edit {
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

/* Эффект для полей при hover */
input:hover,
textarea:hover {
    transform: translateY(-1px);
    transition: all 0.3s ease;
}
</style>
