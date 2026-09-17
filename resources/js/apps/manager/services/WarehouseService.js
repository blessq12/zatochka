import axios from "axios";

const CATEGORY_LABELS = {
    spare_part: "Запчасти",
    consumable: "Расходники",
};

export const warehouseService = {
    categoryLabels: CATEGORY_LABELS,

    categoryLabel(category) {
        return CATEGORY_LABELS[category] || category;
    },

    async list(category = null) {
        const params = {};
        if (category) params.category = category;
        const { data } = await axios.get("/api/warehouse/items", { params });
        return data.data || [];
    },

    async get(id) {
        const { data } = await axios.get(`/api/warehouse/items/${id}`);
        return data;
    },

    async create(payload) {
        const { data } = await axios.post("/api/warehouse/items", payload);
        return data;
    },

    async update(id, payload) {
        const { data } = await axios.put(`/api/warehouse/items/${id}`, payload);
        return data;
    },

    async receive(id, qty) {
        const { data } = await axios.post(`/api/warehouse/items/${id}/receive`, {
            qty,
        });
        return data;
    },

    async getIssueByOrder(orderId) {
        const { data } = await axios.get(
            `/api/warehouse/issues/by-order/${orderId}`,
        );
        return data;
    },

    async applyOrderMaterials(orderId, lines) {
        const { data } = await axios.put(`/api/orders/${orderId}/materials`, {
            lines,
        });
        return data;
    },
};
