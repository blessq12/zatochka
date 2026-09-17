import axios from "axios";

export const STATUS_LABELS = {
    created: "Создан",
    master_assigned: "Мастер назначен",
    in_progress: "В работе",
    waiting_parts: "Ожидает запчасти",
    works_completed: "Работы выполнены",
    ready: "Готов",
    issued: "Выдан",
    cancelled: "Отменён",
};

export const BILLING_LABELS = {
    paid: "Платный",
    warranty: "Гарантийный",
};

export const URGENCY_LABELS = {
    normal: "Обычный",
    urgent: "Срочный",
};

export const KIND_LABELS = {
    sharpening: "Заточка",
    repair: "Ремонт",
};

export function statusLabel(status) {
    return STATUS_LABELS[status] || status || "—";
}

export function allowedTransitions(status) {
    switch (status) {
        case "created":
            return ["cancelled"];
        case "master_assigned":
            return ["cancelled"];
        case "in_progress":
            return ["waiting_parts"];
        case "waiting_parts":
            return ["in_progress"];
        case "works_completed":
            return ["ready"];
        case "ready":
            return ["issued"];
        default:
            return [];
    }
}

export const orderService = {
    async list({ clientId = null, status = null } = {}) {
        const params = {};
        if (clientId != null && clientId !== "") {
            params.client_id = clientId;
        }
        if (status) {
            params.status = status;
        }
        const { data } = await axios.get("/api/orders", { params });
        return data.data || [];
    },

    async get(id) {
        const { data } = await axios.get(`/api/orders/${id}`);
        return data;
    },

    async create(payload) {
        const { data } = await axios.post("/api/orders", payload);
        return data;
    },

    async updateItems(id, items) {
        const { data } = await axios.put(`/api/orders/${id}/items`, { items });
        return data;
    },

    async assignMaster(id, masterId) {
        const { data } = await axios.post(`/api/orders/${id}/assign-master`, {
            master_id: masterId,
        });
        return data;
    },

    async transition(id, status) {
        const { data } = await axios.post(`/api/orders/${id}/transition`, {
            status,
        });
        return data;
    },
};
