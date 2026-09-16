import axios from "axios";

export const clientService = {
    async list(params = {}) {
        const { data } = await axios.get("/api/v1/clients", { params });
        return data.data;
    },
    async get(id) {
        const { data } = await axios.get(`/api/v1/clients/${id}`);
        return data.data;
    },
    async create(payload) {
        const { data } = await axios.post("/api/v1/clients", payload);
        return data.data;
    },
    async update(id, payload) {
        const { data } = await axios.patch(`/api/v1/clients/${id}`, payload);
        return data.data;
    },
};
