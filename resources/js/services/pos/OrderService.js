import axios from "axios";

const POS_ORDERS_BASE = "/api/pos/orders";

/**
 * Сервис для работы с заказами в POS панели мастера.
 * Контракты соответствуют PosController (бэкенд — источник истины).
 */
export const orderService = {
    /**
     * @param {string|null} status — таб воронки: new | active | waiting_parts | completed
     * @returns {Promise<Array>}
     */
    async getOrders(status = null, page = 1, perPage = 20) {
        const params = { page, per_page: perPage };
        if (status) {
            params.status = status;
        }

        const response = await axios.get(POS_ORDERS_BASE, { params });

        return {
            items: response.data.data || [],
            meta: response.data.meta || {
                total: 0,
                page,
                per_page: perPage,
            },
        };
    },

    async getNewOrders() {
        return this.getOrders("new");
    },

    async getActiveOrders() {
        return this.getOrders("active");
    },

    async getWaitingPartsOrders() {
        return this.getOrders("waiting_parts");
    },

    async getCompletedOrders() {
        return this.getOrders("completed");
    },

    /**
     * @returns {Promise<Object>} { new, in_work, waiting_parts, ready }
     */
    async getOrdersCount() {
        const response = await axios.get(`${POS_ORDERS_BASE}/counts`);
        const counts = response.data.data || {};

        return {
            new: counts.new || 0,
            in_work: counts.active || 0,
            waiting_parts: counts.waiting_parts || 0,
            ready: counts.completed || 0,
        };
    },

    async getOrderById(orderId) {
        const response = await axios.get(`${POS_ORDERS_BASE}/${orderId}`);
        return response.data.data;
    },

    async takeToWork(orderId) {
        const response = await axios.post(
            `${POS_ORDERS_BASE}/${orderId}/take-to-work`
        );
        return response.data.data;
    },

    async markWaitingForParts(orderId) {
        const response = await axios.post(
            `${POS_ORDERS_BASE}/${orderId}/waiting-parts`
        );
        return response.data.data;
    },

    async resume(orderId) {
        const response = await axios.post(
            `${POS_ORDERS_BASE}/${orderId}/resume`
        );
        return response.data.data;
    },

    async markReady(orderId) {
        const response = await axios.post(
            `${POS_ORDERS_BASE}/${orderId}/mark-ready`
        );
        return response.data.data;
    },

    async updateInternalNotes(orderId, notes) {
        const response = await axios.patch(
            `${POS_ORDERS_BASE}/${orderId}/internal-notes`,
            { notes }
        );
        return response.data.data;
    },

    async addWork(orderId, description, toolType = null) {
        const response = await axios.post(`${POS_ORDERS_BASE}/${orderId}/works`, {
            description,
            tool_type: toolType,
        });
        return response.data.data;
    },

    async removeWork(orderId, sortOrder) {
        const response = await axios.delete(
            `${POS_ORDERS_BASE}/${orderId}/works`,
            { data: { sort_order: sortOrder } }
        );
        return response.data.data;
    },

    getStatusLabel(status) {
        const statuses = {
            new: "Новый",
            in_work: "В работе",
            waiting_parts: "Ожидание запчастей",
            ready: "Готов к выдаче",
            issued: "Выдан",
            cancelled: "Отменен",
        };
        return statuses[status] || status;
    },

    getTypeLabel(type) {
        const types = {
            repair: "Ремонт",
            sharpening: "Заточка",
            replacement: "Замена",
            maintenance: "Обслуживание",
            warranty: "Гарантия",
        };
        return types[type] || type;
    },

    formatPrice(price) {
        if (!price) return "0";
        return new Intl.NumberFormat("ru-RU").format(price);
    },
};
