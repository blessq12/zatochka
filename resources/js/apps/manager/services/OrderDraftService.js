import axios from "axios";

export const DRAFT_STATUS_LABELS = {
    pending: "Ожидает",
    cancelled: "Отменён",
    promoted: "В заказ",
};

export const DRAFT_SOURCE_LABELS = {
    public: "Сайт",
    client_lk: "ЛК клиента",
};

export function draftStatusLabel(status) {
    return DRAFT_STATUS_LABELS[status] || status || "—";
}

export function draftSourceLabel(source) {
    return DRAFT_SOURCE_LABELS[source] || source || "—";
}

export const orderDraftService = {
    async list(params = {}) {
        const query = {};
        if (params.status) query.status = params.status;
        if (params.source) query.source = params.source;
        if (params.phone) query.phone = params.phone;
        if (params.clientId) query.client_id = params.clientId;
        const { data } = await axios.get("/api/order-drafts", { params: query });
        return data.data || [];
    },

    async get(id) {
        const { data } = await axios.get(`/api/order-drafts/${id}`);
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
