import axios from "axios";

export const DRAFT_STATUS_LABELS = {
    pending: "Ожидает",
    cancelled: "Отменён",
    promoted: "В заказ",
};

export function draftStatusLabel(status) {
    return DRAFT_STATUS_LABELS[status] || status || "—";
}

export const orderDraftService = {
    async list(params = {}) {
        const { data } = await axios.get("/api/order-drafts", { params });
        return data.data || [];
    },

    async get(id) {
        const { data } = await axios.get(`/api/order-drafts/${id}`);
        return data;
    },

    async create(payload) {
        const { data } = await axios.post("/api/order-drafts", payload);
        return data;
    },

    async update(id, payload) {
        const { data } = await axios.put(`/api/order-drafts/${id}`, payload);
        return data;
    },

    async cancel(id) {
        const { data } = await axios.post(`/api/order-drafts/${id}/cancel`);
        return data;
    },

    async promote(id, payload) {
        const { data } = await axios.post(
            `/api/order-drafts/${id}/promote`,
            payload,
        );
        return data;
    },
};
