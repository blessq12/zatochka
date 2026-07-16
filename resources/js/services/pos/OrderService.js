import axios from "axios";
import { mapTaskToPosCard } from "../../composables/mapTaskToPosCard.js";

const TASKS_BASE = "/api/v1/workshop/production-tasks";

const FUNNEL_BY_STATUS = {
    new: "new",
    active: "active",
    waiting_parts: "waiting_parts",
    completed: "completed",
};

/**
 * Facade над Workshop production-tasks для POS UI.
 */
export const orderService = {
    async getOrders(status = null, page = 1, perPage = 20) {
        const funnel = FUNNEL_BY_STATUS[status] || status || "new";
        const response = await axios.get(TASKS_BASE, {
            params: { funnel, page, per_page: perPage },
        });

        return {
            items: (response.data.data || []).map(mapTaskToPosCard),
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

    async getOrdersCount() {
        const response = await axios.get(`${TASKS_BASE}/counts`);
        const counts = response.data.data || {};

        return {
            new: counts.new || 0,
            in_work: counts.active || 0,
            waiting_parts: counts.waitingParts || counts.waiting_parts || 0,
            ready: counts.completed || 0,
        };
    },

    async getOrderById(taskId) {
        const response = await axios.get(`${TASKS_BASE}/${taskId}`);
        return mapTaskToPosCard(response.data.data);
    },

    async takeToWork(taskId) {
        const response = await axios.post(`${TASKS_BASE}/${taskId}/start-work`, {
            description: "Взято в работу",
        });
        return mapTaskToPosCard(response.data.data);
    },

    async markWaitingForParts(taskId) {
        const response = await axios.post(
            `${TASKS_BASE}/${taskId}/waiting-parts`
        );
        return mapTaskToPosCard(response.data.data);
    },

    async resume(taskId) {
        const response = await axios.post(`${TASKS_BASE}/${taskId}/resume`);
        return mapTaskToPosCard(response.data.data);
    },

    async markReady(taskId) {
        const response = await axios.post(`${TASKS_BASE}/${taskId}/finish`);
        return mapTaskToPosCard(response.data.data);
    },

    async updateInternalNotes(taskId, notes) {
        const response = await axios.post(`${TASKS_BASE}/${taskId}/comments`, {
            text: notes,
        });
        return mapTaskToPosCard(response.data.data);
    },

    async addWork(taskId, description, { orderItemId = null, equipmentComponentId = null } = {}) {
        const payload = { text: description };

        if (equipmentComponentId != null) {
            payload.equipmentComponentId = equipmentComponentId;
        } else if (orderItemId != null) {
            payload.orderItemId = orderItemId;
        }

        const response = await axios.post(`${TASKS_BASE}/${taskId}/works`, payload);
        return mapTaskToPosCard(response.data.data);
    },

    async rejectItem(taskId, orderItemId, reason, quantity = 1) {
        const response = await axios.post(`${TASKS_BASE}/${taskId}/reject`, {
            orderItemId,
            reason,
            quantity,
        });
        return mapTaskToPosCard(response.data.data);
    },

    async removeWork(taskId, sortOrderOrId) {
        const card = await this.getOrderById(taskId);
        const work =
            card.works?.find((w) => w.id === sortOrderOrId) ||
            card.works?.find((w) => w.sort_order === sortOrderOrId);

        if (!work) {
            return card;
        }

        const response = await axios.delete(
            `${TASKS_BASE}/${taskId}/works/${work.id}`
        );
        return mapTaskToPosCard(response.data.data);
    },

    getStatusLabel(status) {
        const statuses = {
            new: "Новый",
            in_work: "В работе",
            waiting_parts: "Ожидание запчастей",
            ready: "Выполнено",
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
