import axios from "axios";

const formatEquipmentItem = (item) => {
    const brandModel = [item.brand, item.model].filter(Boolean).join(" ");
    const fullName = brandModel
        ? `${item.name} (${brandModel})`
        : item.name;

    const serialNumbers = Array.isArray(item.serial_numbers)
        ? item.serial_numbers
        : [];

    return {
        ...item,
        full_name: fullName,
        serial_numbers_display: serialNumbers.join(", "),
    };
};

export const equipmentService = {
    /**
     * @param {string} query
     * @returns {Promise<Array>}
     */
    async search(query) {
        const trimmed = query.trim();
        const response = await axios.get("/api/pos/equipment", {
            params: { query: trimmed, page: 1, per_page: 20 },
        });

        return (response.data.data || []).map(formatEquipmentItem);
    },

    /**
     * @param {number|string} equipmentId
     * @returns {Promise<{ equipment: object|null, orders: Array }>}
     */
    async getOrderHistory(equipmentId) {
        const response = await axios.get(
            `/api/pos/equipment/${equipmentId}/orders`
        );

        return {
            equipment: null,
            orders: response.data.data || [],
        };
    },
};
