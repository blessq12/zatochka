import axios from "axios";

export const financeService = {
    async getByOrder(orderId) {
        const { data } = await axios.get(
            `/api/finance/pricings/by-order/${orderId}`,
        );
        return data;
    },

    async upsertByOrder(orderId, lines) {
        const { data } = await axios.put(
            `/api/finance/pricings/by-order/${orderId}`,
            { lines },
        );
        return data;
    },
};
