import axios from "axios";

export const searchService = {
    /**
     * @param {string} q
     * @returns {Promise<{ clients: array, equipments: array }>}
     */
    async search(q) {
        const { data } = await axios.get("/api/manager/search", {
            params: { q },
        });
        return {
            clients: data.clients || [],
            equipments: data.equipments || [],
        };
    },
};
