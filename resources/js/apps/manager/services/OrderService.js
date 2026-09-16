import axios from "axios";

export const orderService = {
    async list(params = {}) {
        const { data } = await axios.get("/api/v1/orders", { params });
        return data.data;
    },
    async get(id) {
        const { data } = await axios.get(`/api/v1/orders/${id}`);
        return data.data;
    },
    async container(id) {
        const { data } = await axios.get(`/api/v1/orders/${id}/container`);
        return data.data;
    },
    async create(payload) {
        const { data } = await axios.post("/api/v1/orders", payload);
        return data.data;
    },
    async cancel(id, reason) {
        const { data } = await axios.post(`/api/v1/orders/${id}/cancel`, { reason });
        return data.data;
    },
    async close(id) {
        const { data } = await axios.post(`/api/v1/orders/${id}/close`);
        return data.data;
    },
    async issue(id, paymentMethod = null) {
        const { data } = await axios.post(`/api/v1/orders/${id}/issue`, { paymentMethod });
        return data.data;
    },
};
