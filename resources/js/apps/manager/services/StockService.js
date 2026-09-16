import axios from "axios";

export const stockService = {
    async list(params = {}) {
        const { data } = await axios.get("/api/v1/stock-items", { params });
        return { items: data.data, meta: data.meta };
    },
    async get(id) {
        const { data } = await axios.get(`/api/v1/stock-items/${id}`);
        return data.data;
    },
    async create(payload) {
        const { data } = await axios.post("/api/v1/stock-items", payload);
        return data.data;
    },
    async receive(id, payload) {
        const { data } = await axios.post(`/api/v1/stock-items/${id}/receive`, payload);
        return data.data;
    },
    async writeOff(id, payload) {
        const { data } = await axios.post(`/api/v1/stock-items/${id}/write-off`, payload);
        return data.data;
    },
};
