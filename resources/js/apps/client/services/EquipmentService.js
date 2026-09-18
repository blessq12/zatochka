import axios from "axios";

export const equipmentService = {
    async list() {
        const { data } = await axios.get("/api/equipments");
        return data.data || [];
    },

    async get(id) {
        const { data } = await axios.get(`/api/equipments/${id}`);
        return data;
    },
};
