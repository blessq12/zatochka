import axios from "axios";

export const equipmentService = {
    async list({ q = null } = {}) {
        const params = {};
        if (q) {
            params.q = q;
        }
        const { data } = await axios.get("/api/equipments", { params });
        return data.data || [];
    },

    async get(id) {
        const { data } = await axios.get(`/api/equipments/${id}`);
        return data;
    },
};
