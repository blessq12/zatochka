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

export const orderService = {
    async list(scope = "active") {
        const { data } = await axios.get("/api/orders", {
            params: { scope },
        });
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

    async review(id, { rating, text = null }) {
        const { data } = await axios.post(`/api/orders/${id}/review`, {
            rating,
            text,
        });
        return data;
    },
};
