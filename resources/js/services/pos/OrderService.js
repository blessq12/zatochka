import axios from "axios";

/**
 * Сервис для работы с заказами в POS панели мастера
 */
export const orderService = {
    /**
     * Получить список заказов с фильтрацией по статусу
     * @param {string|null} status - Статус заказов: 'new', 'active', 'completed' или null для всех
     * @returns {Promise<Array>} Массив заказов
     */
    async getOrders(status = null) {
        try {
            const params = status ? { status } : {};
            const response = await axios.get("/api/pos/orders", { params });
            return response.data.orders || [];
        } catch (error) {
            console.error("Error fetching orders:", error);
            throw error;
        }
    },

    /**
     * Получить новые заказы
     * @returns {Promise<Array>}
     */
    async getNewOrders() {
        return this.getOrders("new");
    },

    /**
     * Получить активные заказы
     * @returns {Promise<Array>}
     */
    async getActiveOrders() {
        return this.getOrders("active");
    },

    /**
     * Получить завершенные заказы
     * @returns {Promise<Array>}
     */
    async getCompletedOrders() {
        return this.getOrders("completed");
    },

    /**
     * Получить счетчики заказов
     * @returns {Promise<Object>} Объект с полями new и in_work
     */
    async getOrdersCount() {
        try {
            const response = await axios.get("/api/pos/orders/count");
            return {
                new: response.data.new || 0,
                in_work: response.data.in_work || 0,
            };
        } catch (error) {
            console.error("Error fetching orders count:", error);
            return { new: 0, in_work: 0 };
        }
    },

    /**
     * Получить метку статуса заказа
     * @param {string} status
     * @returns {string}
     */
    getStatusLabel(status) {
        const statuses = {
            new: "Новый",
            consultation: "Консультация",
            diagnostic: "Диагностика",
            in_work: "В работе",
            waiting_parts: "Ожидание запчастей",
            ready: "Готов",
            issued: "Выдан",
            cancelled: "Отменен",
        };
        return statuses[status] || status;
    },

    /**
     * Получить метку типа заказа
     * @param {string} type
     * @returns {string}
     */
    getTypeLabel(type) {
        const types = {
            repair: "Ремонт",
            sharpening: "Заточка",
            diagnostic: "Диагностика",
            replacement: "Замена",
            maintenance: "Обслуживание",
            consultation: "Консультация",
            warranty: "Гарантия",
        };
        return types[type] || type;
    },

    /**
     * Форматировать цену для отображения
     * @param {number|string} price
     * @returns {string}
     */
    formatPrice(price) {
        if (!price) return "0";
        return new Intl.NumberFormat("ru-RU").format(price);
    },
};