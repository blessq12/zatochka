<script>
import { gsap } from "gsap";
import { useAuthStore } from "../../stores/auth.js";
import { universalFormSchema } from "../../validation/schemas.js";

export default {
    name: "UniversalOrderForm",
    props: {
        initialServiceType: {
            type: String,
            default: null,
            validator: (value) =>
                !value || ["sharpening", "repair"].includes(value),
        },
    },
    data() {
        return {
            serviceType: "sharpening",
            form: {
                name: "",
                phone: "",
                comment: "",
                agreement: true,
                privacy_agreement: true,
                tools_count: "",
                tool_type: "",
                needs_delivery: false,
                delivery_address: "",
                equipment_name: "",
                equipment_type: "",
                problem_description: "",
                urgency: "normal",
            },
            errors: {},
            loading: false,
            success: false,
            error: null,
        };
    },
    computed: {
        // Получение authStore
        authStore() {
            return useAuthStore();
        },

        // Проверка авторизации
        isAuthenticated() {
            return this.authStore?.isAuthenticated || false;
        },

        // Данные пользователя
        userData() {
            return this.authStore?.user || null;
        },

        // Заголовок кнопки
        submitButtonText() {
            if (this.loading) return "Отправляем...";
            return this.serviceType === "sharpening"
                ? "Заказать заточку"
                : "Заказать ремонт";
        },

        // Иконка кнопки
        submitButtonIcon() {
            if (this.loading) return "mdi mdi-loading mdi-spin";
            return this.serviceType === "sharpening"
                ? "mdi mdi-scissors-cutting"
                : "mdi mdi-wrench";
        },
    },
    watch: {
        // Следим за изменениями авторизации
        isAuthenticated: {
            handler(newValue) {
                if (newValue) {
                    this.fillClientData();
                } else {
                    this.clearClientData();
                }
            },
            immediate: true,
        },

        // Следим за изменениями данных пользователя
        userData: {
            handler(newValue) {
                if (newValue && this.isAuthenticated) {
                    this.fillClientData();
                }
            },
            immediate: true,
        },
    },
    mounted() {
        if (this.initialServiceType) {
            this.serviceType = this.initialServiceType;
        }
        this.$nextTick(() => {
            setTimeout(() => {
                this.fillClientData();
            }, 100);
            this.animateFormEnter();
        });
    },
    methods: {
        setServiceType(type) {
            this.serviceType = type;
            this.clearErrors();
        },

        fillClientData() {
            if (this.isAuthenticated && this.userData) {
                const user = this.userData;

                this.form.name = user.full_name || user.name || "";
                this.form.phone = user.phone || "";

                // Анимация заполнения полей
                this.$nextTick(() => {
                    const nameField = this.$el.querySelector(
                        'input[v-model="form.name"]'
                    );
                    const phoneField = this.$el.querySelector(
                        'input[v-model="form.phone"]'
                    );

                    if (nameField && this.form.name) {
                        gsap.fromTo(
                            nameField,
                            { backgroundColor: "#fef3c7" },
                            {
                                backgroundColor: "#f3f4f6",
                                duration: 0.5,
                                delay: 0.2,
                            }
                        );
                    }

                    if (phoneField && this.form.phone) {
                        gsap.fromTo(
                            phoneField,
                            { backgroundColor: "#fef3c7" },
                            {
                                backgroundColor: "#f3f4f6",
                                duration: 0.5,
                                delay: 0.4,
                            }
                        );
                    }
                });
            }
        },

        clearClientData() {
            this.form.name = "";
            this.form.phone = "";
        },

        clearErrors() {
            this.errors = {};
        },

        resetForm() {
            this.form = {
                name: "",
                phone: "",
                comment: "",
                agreement: false,
                privacy_agreement: true,
                tools_count: "",
                tool_type: "",
                needs_delivery: false,
                delivery_address: "",
                equipment_name: "",
                equipment_type: "",
                problem_description: "",
                urgency: "normal",
            };
            this.errors = {};
            this.success = false;
            this.error = null;

            // Заново заполняем данные клиента если авторизован
            this.fillClientData();
        },

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

        // Обработка изменения доставки
        handleDeliveryChange() {
            if (!this.form.needs_delivery) {
                this.form.delivery_address = "";
                this.clearFieldError("delivery_address");
            }
        },

        // Очистка ошибки поля
        clearFieldError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        // Валидация формы
        async validateForm() {
            this.errors = {};

            try {
                // Добавляем serviceType в данные для валидации
                const validationData = {
                    ...this.form,
                    serviceType: this.serviceType,
                };

                await universalFormSchema.validate(validationData, {
                    abortEarly: false,
                });
                return true;
            } catch (error) {
                if (error.inner) {
                    error.inner.forEach((err) => {
                        this.errors[err.path] = err.message;
                    });
                }
                return false;
            }
        },

        // Анимации
        animateFieldError(field) {
            gsap.to(field, {
                x: [-8, 8, -8, 8, -4, 4, 0],
                duration: 0.6,
                ease: "power2.out",
            });
        },

        highlightErrorField(field) {
            gsap.to(field, {
                borderColor: "#ef4444",
                boxShadow: "0 0 0 3px rgba(239, 68, 68, 0.2)",
                duration: 0.3,
                ease: "power2.out",
            });
        },

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

        animateFieldFocus(field) {
            gsap.to(field, {
                scale: 1.02,
                duration: 0.2,
                ease: "power2.out",
            });
        },

        animateFieldBlur(field) {
            gsap.to(field, {
                scale: 1,
                duration: 0.2,
                ease: "power2.out",
            });
        },

        animateButtonLoading() {
            gsap.to(this.$refs.submitButton, {
                scale: 0.95,
                duration: 0.2,
                ease: "power2.out",
            });
        },

        animateButtonReset() {
            gsap.to(this.$refs.submitButton, {
                scale: 1,
                duration: 0.2,
                ease: "power2.out",
            });
        },

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
            const isValid = await this.validateForm();
            if (!isValid) {
                Object.keys(this.errors).forEach((fieldName, index) => {
                    const field = this.$el.querySelector(
                        `[v-model="form.${fieldName}"]`
                    );
                    if (field) {
                        gsap.delayedCall(index * 0.1, () => {
                            this.animateFieldError(field);
                            this.highlightErrorField(field);
                        });
                    }
                });

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

            this.animateButtonLoading();

            try {
                const token = localStorage.getItem("client_token");

                const payload = {
                    service_type: this.serviceType,
                    client_name: this.form.name,
                    client_phone: this.form.phone,
                    agreement: this.form.agreement,
                    privacy_agreement: this.form.privacy_agreement,
                };

                if (this.serviceType === "sharpening") {
                    payload.tool_type = this.form.tool_type;
                    payload.total_tools_count = this.form.tools_count;
                    payload.problem_description = this.form.comment;
                    payload.needs_delivery = this.form.needs_delivery;
                    if (this.form.needs_delivery) {
                        if (
                            !this.form.delivery_address ||
                            this.form.delivery_address.trim() === ""
                        ) {
                            this.error = "Укажите адрес доставки";
                            return;
                        }
                        payload.delivery_address = this.form.delivery_address;
                    }
                } else if (this.serviceType === "repair") {
                    payload.equipment_type = this.form.equipment_type;
                    payload.equipment_name = this.form.equipment_name;
                    payload.problem_description = this.form.problem_description;
                    payload.urgency = this.form.urgency;
                    payload.needs_delivery = this.form.needs_delivery;
                    if (this.form.needs_delivery) {
                        if (
                            !this.form.delivery_address ||
                            this.form.delivery_address.trim() === ""
                        ) {
                            this.error = "Укажите адрес доставки";
                            return;
                        }
                        payload.delivery_address = this.form.delivery_address;
                    }
                }

                const config = {
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                };

                // Добавляем токен авторизации если есть
                if (token) {
                    config.headers["Authorization"] = `Bearer ${token}`;
                }

                const response = await axios.post(
                    "/api/orders",
                    payload,
                    config
                );

                this.success = true;
                this.resetForm();

                // Показываем тост об успешном создании заказа
                if (window.toastService) {
                    window.toastService.success(
                        "Заказ успешно создан! Мы свяжемся с вами в ближайшее время."
                    );
                }

                this.$nextTick(() => {
                    this.animateSuccess();
                });

                // Эмитим событие о создании заказа
                this.$emit("order-created");
            } catch (error) {
                console.error("=== FORM SUBMISSION ERROR ===");
                console.error("Error object:", error);
                console.error("Error message:", error.message);
                console.error("Error response:", error.response);
                console.error("Error status:", error.response?.status);
                console.error("Error status text:", error.response?.statusText);
                console.error("Error data:", error.response?.data);
                console.error("Error headers:", error.response?.headers);
                console.error("Request config:", error.config);
                console.error("Request data:", error.config?.data);
                console.error("=== END ERROR LOG ===");

                if (error.response) {
                    // Сервер ответил с ошибкой
                    const { status, data } = error.response;

                    if (status === 422) {
                        // Ошибка валидации
                        if (data.errors) {
                            this.errors = data.errors;
                            // Показываем тост с ошибкой валидации
                            if (window.toastService) {
                                window.toastService.validationError(
                                    "Проверьте правильность заполнения формы"
                                );
                            }
                        } else {
                            this.error =
                                data.message || "Ошибка валидации данных";
                            if (window.toastService) {
                                window.toastService.error(this.error);
                            }
                        }
                    } else {
                        // Другие ошибки сервера
                        this.error =
                            data.message || `Ошибка сервера (${status})`;
                        if (window.toastService) {
                            window.toastService.serverError();
                        }
                    }
                } else if (error.request) {
                    // Запрос был отправлен, но ответа не получено
                    this.error =
                        "Нет ответа от сервера. Проверьте подключение к интернету.";
                    if (window.toastService) {
                        window.toastService.networkError();
                    }
                } else {
                    // Ошибка при настройке запроса
                    this.error = "Ошибка при отправке запроса";
                    if (window.toastService) {
                        window.toastService.error(this.error);
                    }
                }

                // Анимация ошибки
                this.$nextTick(() => {
                    this.animateError();
                });
            } finally {
                this.loading = false;
                this.animateButtonReset();
            }
        },
    },
};
</script>

<template>
    <div class="space-y-6" ref="formContainer">
        <!-- Переключатель типа услуги -->
        <div v-if="!initialServiceType" class="service-type-selector mb-8">
            <div class="grid grid-cols-2 gap-4">
                <button
                    @click="setServiceType('sharpening')"
                    :class="[
                        'flex items-center justify-center p-4 rounded-lg border-2 transition-all duration-300',
                        serviceType === 'sharpening'
                            ? 'border-accent bg-accent/10 text-accent'
                            : 'border-gray-200 dark:border-gray-700 hover:border-accent/50',
                    ]"
                    ref="sharpeningBtn"
                >
                    <i class="mdi mdi-scissors-cutting text-2xl mr-3"></i>
                    <div class="text-left">
                        <div class="font-semibold">Заточка</div>
                        <div class="text-sm opacity-75">Инструментов</div>
                    </div>
                </button>

                <button
                    @click="setServiceType('repair')"
                    :class="[
                        'flex items-center justify-center p-4 rounded-lg border-2 transition-all duration-300',
                        serviceType === 'repair'
                            ? 'border-accent bg-accent/10 text-accent'
                            : 'border-gray-200 dark:border-gray-700 hover:border-accent/50',
                    ]"
                    ref="repairBtn"
                >
                    <i class="mdi mdi-wrench text-2xl mr-3"></i>
                    <div class="text-left">
                        <div class="font-semibold">Ремонт</div>
                        <div class="text-sm opacity-75">Оборудования</div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Уведомления -->
        <div
            v-if="serviceType === 'sharpening'"
            class="bg-green-100 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-r-lg mb-6"
            ref="sharpeningNotification"
        >
            <div class="flex items-center">
                <i
                    class="mdi mdi-truck text-green-600 dark:text-green-400 text-2xl mr-3"
                ></i>
                <div>
                    <p class="font-bold text-green-800 dark:text-green-200">
                        Бесплатная доставка!
                    </p>
                    <p class="text-green-700 dark:text-green-300">
                        От 6 маникюрных или от 3 парикмахерских/грумерских
                        инструментов
                    </p>
                </div>
            </div>
        </div>

        <div
            v-if="serviceType === 'repair'"
            class="bg-yellow-100 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 rounded-r-lg mb-6"
            ref="repairNotification"
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
            <!-- Поля для заточки -->
            <div
                v-if="serviceType === 'sharpening'"
                class="sharpening-fields space-y-6"
            >
                <!-- Количество инструментов -->
                <div class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >
                        Количество инструментов
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            min="1"
                            class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                            v-model="form.tools_count"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                    errors.tools_count,
                            }"
                            @focus="handleFieldFocus"
                            @blur="handleFieldBlur"
                            required
                        />
                        <i
                            class="mdi mdi-numeric absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                        ></i>
                    </div>
                    <span
                        v-if="errors.tools_count"
                        class="text-red-500 text-sm font-medium"
                        ref="errorToolsCount"
                    >
                        {{ errors.tools_count }}
                    </span>
                </div>

                <!-- Тип инструментов -->
                <div class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >
                        Тип инструментов
                    </label>
                    <div class="relative">
                        <select
                            class="w-full px-4 py-3 pl-12 pr-10 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300 appearance-none"
                            v-model="form.tool_type"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                    errors.tool_type,
                            }"
                            @focus="handleFieldFocus"
                            @blur="handleFieldBlur"
                            required
                        >
                            <option value="">Выберите тип инструментов</option>
                            <option value="manicure">Маникюрные</option>
                            <option value="hair">Парикмахерские</option>
                            <option value="grooming">Груминг</option>
                            <option value="other">Другое</option>
                        </select>
                        <i
                            class="mdi mdi-tools absolute left-3 top-1/2 transform -translate-y-1/2 text-accent text-lg"
                        ></i>
                        <i
                            class="mdi mdi-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"
                        ></i>
                    </div>
                    <span
                        v-if="errors.tool_type"
                        class="text-red-500 text-sm font-medium"
                        ref="errorToolType"
                    >
                        {{ errors.tool_type }}
                    </span>
                </div>

                <!-- Нужна ли доставка -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            class="w-5 h-5 text-accent accent-accent"
                            v-model="form.needs_delivery"
                            @change="handleDeliveryChange"
                        />
                        <label class="dark:text-white text-sm font-semibold">
                            Нужна доставка
                        </label>
                    </div>
                </div>

                <!-- Адрес доставки (если нужна) -->
                <div v-if="form.needs_delivery" class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >
                        Адрес доставки
                    </label>
                    <div class="relative">
                        <textarea
                            class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300 resize-y min-h-[100px]"
                            rows="3"
                            placeholder="Укажите полный адрес для доставки"
                            v-model="form.delivery_address"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                    errors.delivery_address,
                            }"
                            @focus="handleFieldFocus"
                            @blur="handleFieldBlur"
                            required
                        ></textarea>
                        <i
                            class="mdi mdi-map-marker absolute left-3 top-4 text-accent text-lg"
                        ></i>
                    </div>
                    <span
                        v-if="errors.delivery_address"
                        class="text-red-500 text-sm font-medium"
                        ref="errorDeliveryAddress"
                    >
                        {{ errors.delivery_address }}
                    </span>
                </div>
            </div>

            <!-- Поля для ремонта -->
            <div
                v-if="serviceType === 'repair'"
                class="repair-fields space-y-6"
            >
                <!-- Наименование аппарата -->
                <div class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >
                        Наименование аппарата
                    </label>
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
                    >
                        {{ errors.equipment_name }}
                    </span>
                </div>

                <!-- Тип оборудования -->
                <div class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >
                        Тип оборудования
                    </label>
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
                            <option value="scissors">
                                Электрические ножницы
                            </option>
                            <option value="trimmer">Триммер</option>
                            <option value="ultrasonic">
                                Ультразвуковая ванна
                            </option>
                            <option value="razor">Бритва</option>
                            <option value="shaver">Электробритва</option>
                            <option value="epilator">Эпилятор</option>
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
                    >
                        {{ errors.equipment_type }}
                    </span>
                </div>

                <!-- Описание проблемы -->
                <div class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >
                        Описание проблемы
                    </label>
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
                    >
                        {{ errors.problem_description }}
                    </span>
                </div>

                <!-- Срочность -->
                <div class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >
                        Срочность ремонта
                    </label>
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
                    >
                        {{ errors.urgency }}
                    </span>
                </div>

                <!-- Нужна ли доставка -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            class="w-5 h-5 text-accent accent-accent"
                            v-model="form.needs_delivery"
                            @change="handleDeliveryChange"
                        />
                        <label class="dark:text-white text-sm font-semibold">
                            Нужна доставка
                        </label>
                    </div>
                </div>

                <!-- Адрес доставки (если нужна) -->
                <div v-if="form.needs_delivery" class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >
                        Адрес доставки
                    </label>
                    <div class="relative">
                        <textarea
                            class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300 resize-y min-h-[100px]"
                            rows="3"
                            placeholder="Укажите полный адрес для доставки"
                            v-model="form.delivery_address"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                    errors.delivery_address,
                            }"
                            @focus="handleFieldFocus"
                            @blur="handleFieldBlur"
                            required
                        ></textarea>
                        <i
                            class="mdi mdi-map-marker absolute left-3 top-4 text-accent text-lg"
                        ></i>
                    </div>
                    <span
                        v-if="errors.delivery_address"
                        class="text-red-500 text-sm font-medium"
                        ref="errorDeliveryAddress"
                    >
                        {{ errors.delivery_address }}
                    </span>
                </div>
            </div>

            <!-- Общие поля -->
            <div class="common-fields space-y-6">
                <!-- Имя и телефон -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label
                            class="block text-sm font-semibold text-gray-700 dark:text-white"
                        >
                            Ваше имя
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                                v-model="form.name"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                        errors.name,
                                    'bg-gray-100 dark:bg-gray-600 cursor-not-allowed':
                                        isAuthenticated,
                                }"
                                :readonly="isAuthenticated"
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
                        >
                            {{ errors.name }}
                        </span>
                        <span
                            v-else-if="isAuthenticated"
                            class="text-green-600 dark:text-green-400 text-sm"
                        >
                            <i class="mdi mdi-check-circle mr-1"></i>
                            Заполнено автоматически
                        </span>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="block text-sm font-semibold text-gray-700 dark:text-white"
                        >
                            Телефон
                        </label>
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
                                    'bg-gray-100 dark:bg-gray-600 cursor-not-allowed':
                                        isAuthenticated,
                                }"
                                :readonly="isAuthenticated"
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
                        >
                            {{ errors.phone }}
                        </span>
                        <span
                            v-else-if="isAuthenticated"
                            class="text-green-600 dark:text-green-400 text-sm"
                        >
                            <i class="mdi mdi-check-circle mr-1"></i>
                            Заполнено автоматически
                        </span>
                    </div>
                </div>

                <!-- Комментарий -->
                <div class="space-y-2">
                    <label
                        class="block text-sm font-semibold text-gray-700 dark:text-white"
                    >
                        Комментарий
                    </label>
                    <div class="relative">
                        <textarea
                            class="w-full px-4 py-3 pl-12 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300 resize-y min-h-[100px]"
                            rows="4"
                            placeholder="Дополнительная информация о заказе"
                            v-model="form.comment"
                            @focus="handleFieldFocus"
                            @blur="handleFieldBlur"
                        ></textarea>
                        <i
                            class="mdi mdi-comment-text absolute left-3 top-4 text-accent text-lg"
                        ></i>
                    </div>
                    <span
                        v-if="errors.comment"
                        class="text-red-500 text-sm font-medium"
                        ref="errorComment"
                    >
                        {{ errors.comment }}
                    </span>
                </div>

                <!-- Согласия -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            class="w-5 h-5 text-accent accent-accent"
                            v-model="form.agreement"
                            :class="{ 'border-red-500': errors.agreement }"
                            required
                        />
                        <label class="dark:text-white text-sm">
                            Я ознакомлен с
                            <a
                                href="/delivery"
                                target="_blank"
                                class="text-accent hover:underline"
                            >
                                условиями доставки
                            </a>
                        </label>
                    </div>
                    <span
                        v-if="errors.agreement"
                        class="text-red-500 text-sm font-medium"
                        ref="errorAgreement"
                    >
                        {{ errors.agreement }}
                    </span>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            class="w-5 h-5 text-accent accent-accent"
                            v-model="form.privacy_agreement"
                            :class="{
                                'border-red-500': errors.privacy_agreement,
                            }"
                            required
                        />
                        <label class="dark:text-white text-sm">
                            Даю согласие на
                            <a
                                href="/privacy-policy"
                                target="_blank"
                                class="text-accent hover:underline"
                            >
                                обработку персональных данных
                            </a>
                        </label>
                    </div>
                    <span
                        v-if="errors.privacy_agreement"
                        class="text-red-500 text-sm font-medium"
                        ref="errorPrivacyAgreement"
                    >
                        {{ errors.privacy_agreement }}
                    </span>
                </div>
            </div>

            <!-- Кнопка отправки -->
            <button
                type="submit"
                class="w-full bg-gradient-to-r from-accent to-pink-600 text-white font-semibold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center"
                :disabled="loading"
                ref="submitButton"
            >
                <i :class="submitButtonIcon + ' mr-2'"></i>
                {{ submitButtonText }}
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
                        Мы свяжемся с вами в ближайшее время
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

<style scoped>
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
