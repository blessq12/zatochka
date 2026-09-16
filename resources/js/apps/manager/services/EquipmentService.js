import axios from "axios";

export const equipmentService = {
    async list(params = {}) {
        const { data } = await axios.get("/api/v1/equipment", { params });
        return { items: data.data, meta: data.meta };
    },
    async get(id) {
        const { data } = await axios.get(`/api/v1/equipment/${id}`);
        return data.data;
    },
    async create(payload) {
        const { data } = await axios.post("/api/v1/equipment", payload);
        return data.data;
    },
};
