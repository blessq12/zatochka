import axios from "axios";

export const KIND_LABELS = {
    sharpening: "Заточка",
    repair: "Ремонт",
};

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

export function statusLabel(status) {
    return STATUS_LABELS[status] || status || "—";
}

export const orderService = {
    async listAssigned() {
        const { data } = await axios.get("/api/orders/assigned");
        return data.data || [];
    },

    async get(id) {
        const { data } = await axios.get(`/api/orders/${id}`);
        return data;
    },
};
