import { defineStore } from "pinia";

export const useOrdersStore = defineStore("orders", {
    state: () => ({
        // Состояние заявок
        orders: [],
        currentOrder: null,
        loading: false,
        error: null,

        // Состояние формы
        form: {
            serviceType: "sharpening",
            name: "",
            phone: "",
            agreement: false,
            privacy_agreement: true,

            // Поля для заточки
            tools_count: "",
            tool_type: "",
            needs_delivery: false,
            delivery_address: "",

            // Поля для ремонта
            equipment_name: "",
            equipment_type: "",
            problem_description: "",
            urgency: "normal",

            // Общие поля
            comment: "",
        },

        // Валидация
        errors: {},

        // UI состояние
        isSubmitting: false,
        isSuccess: false,
    }),

    getters: {
        // Проверка авторизации
        isAuthenticated: (state) => {
            const token = localStorage.getItem("client_token");
            return !!token;
        },

        // Получение токена
        authToken: () => {
            return localStorage.getItem("client_token");
        },

        // Проверка валидности формы
        isFormValid: (state) => {
            return Object.keys(state.errors).length === 0;
        },

        // Получение заголовков для API
        apiHeaders: (state) => {
            const headers = {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
            };

            if (state.authToken) {
                headers["Authorization"] = `Bearer ${state.authToken}`;
            }

            return headers;
        },
    },

    actions: {
        // Инициализация стора
        async init() {
            if (this.isAuthenticated) {
                await this.loadOrders();
            }
        },

        // Загрузка заказов пользователя
        async loadOrders() {
            this.loading = true;
            this.error = null;

            try {
                const response = await fetch("/api/client/orders", {
                    headers: this.apiHeaders,
                });

                if (response.ok) {
                    const data = await response.json();
                    this.orders = data.data || [];
                } else {
                    throw new Error("Ошибка загрузки заказов");
                }
            } catch (err) {
                this.error = err.message;
                console.error("Load orders error:", err);
            } finally {
                this.loading = false;
            }
        },

        // Установка типа услуги
        setServiceType(type) {
            this.form.serviceType = type;
            this.clearErrors();
        },

        // Обновление поля формы
        updateField(field, value) {
            this.form[field] = value;
            this.clearFieldError(field);
        },

        // Валидация формы
        validateForm() {
            this.errors = {};

            // Общие поля
            if (!this.form.name) this.errors.name = "Укажите имя";
            if (!this.form.phone) this.errors.phone = "Укажите телефон";
            if (!this.form.agreement)
                this.errors.agreement = "Необходимо согласие";
            if (!this.form.privacy_agreement)
                this.errors.privacy_agreement =
                    "Необходимо согласие на обработку данных";

            // Условные поля
            if (this.form.serviceType === "sharpening") {
                if (!this.form.tools_count)
                    this.errors.tools_count = "Укажите количество";
                if (!this.form.tool_type)
                    this.errors.tool_type = "Выберите тип инструментов";
                if (this.form.needs_delivery && !this.form.delivery_address) {
                    this.errors.delivery_address = "Укажите адрес доставки";
                }
            }

            if (this.form.serviceType === "repair") {
                if (!this.form.equipment_name)
                    this.errors.equipment_name = "Укажите аппарат";
                if (!this.form.equipment_type)
                    this.errors.equipment_type = "Выберите тип оборудования";
                if (!this.form.problem_description)
                    this.errors.problem_description = "Опишите проблему";
            }

            return Object.keys(this.errors).length === 0;
        },

        // Очистка ошибки поля
        clearFieldError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        // Очистка всех ошибок
        clearErrors() {
            this.errors = {};
        },

        // Сброс формы
        resetForm() {
            this.form = {
                serviceType: "sharpening",
                name: "",
                phone: "",
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
                comment: "",
            };
            this.clearErrors();
            this.isSuccess = false;
        },

        // Заполнение данных клиента
        fillClientData(clientData) {
            if (clientData) {
                this.form.name = clientData.full_name || "";
                this.form.phone = clientData.phone || "";
            }
        },

        // Отправка заявки
        async submitOrder() {
            if (!this.validateForm()) {
                return { success: false, errors: this.errors };
            }

            this.isSubmitting = true;
            this.error = null;

            try {
                const payload = this.buildPayload();

                const response = await fetch("/api/orders", {
                    method: "POST",
                    headers: this.apiHeaders,
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (response.ok) {
                    this.isSuccess = true;
                    this.resetForm();

                    // Обновляем список заказов если авторизованы
                    if (this.isAuthenticated) {
                        await this.loadOrders();
                    }

                    return { success: true, order: data.order };
                } else {
                    this.error = data.message || "Ошибка при отправке заявки";
                    return { success: false, error: this.error };
                }
            } catch (err) {
                this.error = "Произошла ошибка при отправке заявки";
                console.error("Submit order error:", err);
                return { success: false, error: this.error };
            } finally {
                this.isSubmitting = false;
            }
        },

        // Построение payload для API
        buildPayload() {
            const payload = {
                service_type: this.form.serviceType,
                client_name: this.form.name,
                client_phone: this.form.phone,
                agreement: this.form.agreement,
                privacy_agreement: this.form.privacy_agreement,
            };

            if (this.form.serviceType === "sharpening") {
                payload.tool_type = this.form.tool_type;
                payload.total_tools_count = this.form.tools_count;
                payload.problem_description = this.form.comment;
                payload.needs_delivery = this.form.needs_delivery;
                if (this.form.needs_delivery) {
                    payload.delivery_address = this.form.delivery_address;
                }
            } else if (this.form.serviceType === "repair") {
                payload.tool_type = this.form.equipment_type;
                payload.equipment_name = this.form.equipment_name;
                payload.problem_description = this.form.problem_description;
                payload.urgency = this.form.urgency;
            }

            return payload;
        },

        // Получение заказа по ID
        async getOrder(id) {
            this.loading = true;

            try {
                const response = await fetch(`/api/orders/${id}`, {
                    headers: this.apiHeaders,
                });

                if (response.ok) {
                    const data = await response.json();
                    this.currentOrder = data.order;
                    return data.order;
                } else {
                    throw new Error("Заказ не найден");
                }
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.loading = false;
            }
        },
    },
});
