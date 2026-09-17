import axios from "axios";

export const equipmentService = {
    async list() {
        const { data } = await axios.get("/api/equipments");
        return data.data || [];
    },
};
