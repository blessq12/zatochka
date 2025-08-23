<template>
    <div class="hero-card rounded-2xl shadow-lg p-8" ref="formContainer">
        <!-- Уведомление о бесплатной доставке -->
        <div
            class="bg-yellow-100 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 mb-8 rounded-r-lg"
            ref="notification"
        >
            <div class="flex items-center">
                <i
                    class="mdi mdi-truck text-yellow-600 dark:text-yellow-400 text-2xl mr-3"
                ></i>
                <div>
                    <p class="font-bold text-yellow-800 dark:text-yellow-200">
                        Доставка бесплатная!
                    </p>
                    <p class="text-yellow-700 dark:text-yellow-300">
                        При заказе ремонта оборудования доставка в обе стороны
                        бесплатная
                    </p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submitForm" class="space-y-6" ref="form">
            <!-- Наименование аппарата -->
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >Наименование аппарата</label
                >
                <div class="relative">
                    <input
                        type="text"
                        class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                        placeholder="Например: Машинка для стрижки"
                        v-model="form.equipment_name"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                errors.equipment_name,
                        }"
                        @focus="handleFieldFocus"
                        @blur="handleFieldBlur"
                        required
                    />
                    <i
                        class="mdi mdi-tools absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                    ></i>
                </div>
                <span
                    v-if="errors.equipment_name"
                    class="text-red-500 text-sm font-medium"
                    ref="errorEquipmentName"
                    >{{ errors.equipment_name }}</span
                >
            </div>

            <!-- Тип оборудования -->
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >Тип оборудования</label
                >
                <div class="relative">
                    <select
                        class="w-full px-4 py-3 pl-12 pr-10 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300 appearance-none"
                        v-model="form.equipment_type"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                errors.equipment_type,
                        }"
                        @focus="handleFieldFocus"
                        @blur="handleFieldBlur"
                        required
                    >
                        <option value="">Выберите тип оборудования</option>
                        <option value="clipper">Машинка для стрижки</option>
                        <option value="dryer">Фен</option>
                        <option value="scissors">Электрические ножницы</option>
                        <option value="trimmer">Триммер</option>
                        <option value="ultrasonic">Ультразвуковая ванна</option>
                        <option value="other">Другое</option>
                    </select>
                    <i
                        class="mdi mdi-cog absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                    ></i>
                    <i
                        class="mdi mdi-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"
                    ></i>
                </div>
                <span
                    v-if="errors.equipment_type"
                    class="text-red-500 text-sm font-medium"
                    ref="errorEquipmentType"
                    >{{ errors.equipment_type }}</span
                >
            </div>

            <!-- Описание проблемы -->
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >Описание проблемы</label
                >
                <div class="relative">
                    <textarea
                        class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300 resize-y min-h-[100px]"
                        rows="4"
                        placeholder="Опишите что происходит с оборудованием"
                        v-model="form.problem_description"
                        :class="{
                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                errors.problem_description,
                        }"
                        @focus="handleFieldFocus"
                        @blur="handleFieldBlur"
                        required
                    ></textarea>
                    <i
                        class="mdi mdi-alert-circle absolute left-3 top-4 text-accent text-lg"
                    ></i>
                </div>
                <span
                    v-if="errors.problem_description"
                    class="text-red-500 text-sm font-medium"
                    ref="errorProblemDescription"
                    >{{ errors.problem_description }}</span
                >
            </div>

            <!-- Имя и телефон -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                        >Ваше имя</label
                    >
                    <div class="relative">
                        <input
                            type="text"
                            class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                            v-model="form.name"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                    errors.name,
                            }"
                            @focus="handleFieldFocus"
                            @blur="handleFieldBlur"
                            required
                        />
                        <i
                            class="mdi mdi-account absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                        ></i>
                    </div>
                    <span
                        v-if="errors.name"
                        class="text-red-500 text-sm font-medium"
                        ref="errorName"
                        >{{ errors.name }}</span
                    >
                </div>

                <div class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                        >Телефон</label
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
            </div>

            <!-- Срочность -->
            <div class="space-y-2">
                <label
                    class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >Срочность ремонта</label
                >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label
                        class="flex items-center p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        <input
                            type="radio"
                            name="urgency"
                            value="normal"
                            v-model="form.urgency"
                            class="mr-3 w-5 h-5 text-accent accent-accent"
                        />
                        <div>
                            <div class="font-semibold dark:text-white">
                                Обычный ремонт
                            </div>
                            <div
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                1-5 дней
                            </div>
                        </div>
                    </label>
                    <label
                        class="flex items-center p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        <input
                            type="radio"
                            name="urgency"
                            value="urgent"
                            v-model="form.urgency"
                            class="mr-3 w-5 h-5 text-accent accent-accent"
                        />
                        <div>
                            <div class="font-semibold dark:text-white">
                                Срочный ремонт
                            </div>
                            <div
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                24 часа (+50%)
                            </div>
                        </div>
                    </label>
                </div>
                <span
                    v-if="errors.urgency"
                    class="text-red-500 text-sm font-medium"
                    ref="errorUrgency"
                    >{{ errors.urgency }}</span
                >
            </div>

            <!-- Согласие -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        class="w-5 h-5 text-accent accent-accent"
                        v-model="form.agreement"
                        :class="{ 'border-red-500': errors.agreement }"
                        required
                    />
                    <label class="dark:text-white text-sm"
                        >Я ознакомлен с
                        <a
                            href="/delivery"
                            target="_blank"
                            class="text-accent hover:underline"
                            >условиями доставки</a
                        ></label
                    >
                </div>
                <span
                    v-if="errors.agreement"
                    class="text-red-500 text-sm font-medium"
                    ref="errorAgreement"
                    >{{ errors.agreement }}</span
                >

                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        class="w-5 h-5 text-accent accent-accent"
                        v-model="form.privacy_agreement"
                        :class="{ 'border-red-500': errors.privacy_agreement }"
                        required
                    />
                    <label class="dark:text-white text-sm"
                        >Даю согласие на
                        <a
                            href="/privacy-policy"
                            target="_blank"
                            class="text-accent hover:underline"
                            >обработку персональных данных</a
                        ></label
                    >
                </div>
                <span
                    v-if="errors.privacy_agreement"
                    class="text-red-500 text-sm font-medium"
                    ref="errorPrivacyAgreement"
                    >{{ errors.privacy_agreement }}</span
                >
            </div>

            <!-- Кнопка отправки -->
            <button
                type="submit"
                class="w-full bg-gradient-to-r from-accent to-pink-600 text-white font-semibold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center"
                :disabled="loading"
                ref="submitButton"
            >
                <i v-if="loading" class="mdi mdi-loading mdi-spin mr-2"></i>
                <i v-else class="mdi mdi-wrench mr-2"></i>
                {{ loading ? "Отправляем..." : "Заказать ремонт" }}
            </button>
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
                        Заявка отправлена!
                    </p>
                    <p class="text-green-700 dark:text-green-300">
                        Мы свяжемся с вами в ближайшее время для уточнения
                        деталей
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
import { repairFormSchema, validateForm } from "../../validation/schemas.js";

export default {
    name: "RepairOrderForm",
    data() {
        return {
            form: {
                equipment_name: "",
                equipment_type: "",
                problem_description: "",
                name: "",
                phone: "",
                urgency: "normal",
                agreement: false,
                privacy_agreement: true,
            },
            errors: {},
            loading: false,
            success: false,
            error: null,
        };
    },
    mounted() {
        // Анимация появления формы
        this.$nextTick(() => {
            this.animateFormEnter();
            this.animateNotification();
        });
    },
    methods: {
        // Анимация появления формы
        animateFormEnter() {
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

        // Анимация уведомления
        animateNotification() {
            gsap.fromTo(
                this.$refs.notification,
                {
                    opacity: 0,
                    y: -20,
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

        // Анимация кнопки загрузки
        animateButtonLoading() {
            gsap.to(this.$refs.submitButton, {
                scale: 0.95,
                duration: 0.2,
                ease: "power2.out",
            });
        },

        // Анимация сброса кнопки
        animateButtonReset() {
            gsap.to(this.$refs.submitButton, {
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

        async submitForm() {
            // Валидация при сабмите
            const result = await validateForm(repairFormSchema, this.form);

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

            this.loading = true;
            this.error = null;

            // Анимация кнопки загрузки
            this.animateButtonLoading();

            try {
                const response = await fetch("/api/orders", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({
                        service_type: "repair",
                        tool_type: this.form.equipment_type,
                        problem_description: this.form.problem_description,
                        client_name: this.form.name,
                        client_phone: this.form.phone,
                        equipment_name: this.form.equipment_name,
                        urgency: this.form.urgency,
                        agreement: this.form.agreement,
                        privacy_agreement: this.form.privacy_agreement,
                    }),
                });

                if (response.ok) {
                    this.success = true;
                    this.resetForm();

                    // Анимация успешного сообщения
                    this.$nextTick(() => {
                        this.animateSuccess();
                    });
                } else {
                    const data = await response.json();
                    this.error =
                        data.message || "Произошла ошибка при отправке заявки";

                    // Анимация ошибки
                    this.$nextTick(() => {
                        this.animateError();
                    });
                }
            } catch (err) {
                this.error = "Произошла ошибка при отправке заявки";
                console.error("Form submission error:", err);

                // Анимация ошибки
                this.$nextTick(() => {
                    this.animateError();
                });
            } finally {
                this.loading = false;
                this.animateButtonReset();
            }
        },

        resetForm() {
            this.form = {
                equipment_name: "",
                equipment_type: "",
                problem_description: "",
                name: "",
                phone: "",
                urgency: "normal",
                agreement: false,
                privacy_agreement: true,
            };
            this.errors = {};

            // Анимация сброса формы
            gsap.to(this.$refs.formContainer, {
                opacity: 0.5,
                scale: 0.98,
                duration: 0.2,
                ease: "power2.out",
                onComplete: () => {
                    gsap.to(this.$refs.formContainer, {
                        opacity: 1,
                        scale: 1,
                        duration: 0.3,
                        ease: "back.out(1.7)",
                    });
                },
            });
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

/* Анимация для радио кнопок */
input[type="radio"]:checked + div {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}

/* Анимация для чекбокса */
input[type="checkbox"]:checked {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}
</style>
