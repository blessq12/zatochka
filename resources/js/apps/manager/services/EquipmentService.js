import axios from "axios";

export const equipmentService = {
    async list(clientId = null) {
        const params = {};
        if (clientId != null && clientId !== "") {
            params.client_id = clientId;
        }
        const { data } = await axios.get("/api/equipments", { params });
        return data.data || [];
    },

    async get(id) {
        const { data } = await axios.get(`/api/equipments/${id}`);
        return data;
    },

    async create(payload) {
        const { data } = await axios.post("/api/equipments", payload);
        return data;
    },

    async update(id, payload) {
        const { data } = await axios.put(`/api/equipments/${id}`, payload);
        return data;
    },

    async remove(id) {
        await axios.delete(`/api/equipments/${id}`);
    },
};
