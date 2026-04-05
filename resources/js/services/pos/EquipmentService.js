import axios from "axios";

export const equipmentService = {
    /**
     * @param {string} q
     * @returns {Promise<Array>}
     */
    async search(q) {
        const response = await axios.get("/api/pos/equipment/search", {
            params: { q: q.trim() },
        });
        return response.data.equipment || [];
    },

    /**
     * @param {number|string} equipmentId
     * @returns {Promise<{ equipment: object, orders: Array }>}
     */
    async getOrderHistory(equipmentId) {
        const response = await axios.get(
            `/api/pos/equipment/${equipmentId}/orders`
        );
        return {
            equipment: response.data.equipment,
            orders: response.data.orders || [],
        };
    },
};
