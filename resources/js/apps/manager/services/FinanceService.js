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

    async listCashEntries({ from = null, to = null, type = null } = {}) {
        const params = {};
        if (from) params.from = from;
        if (to) params.to = to;
        if (type) params.type = type;
        const { data } = await axios.get("/api/finance/cash-entries", {
            params,
        });
        return data;
    },

    async createCashEntry(payload) {
        const { data } = await axios.post("/api/finance/cash-entries", payload);
        return data;
    },

    async deleteCashEntry(id) {
        await axios.delete(`/api/finance/cash-entries/${id}`);
    },

    async listGoals() {
        const { data } = await axios.get("/api/finance/goals");
        return data.data || [];
    },

    async createGoal(payload) {
        const { data } = await axios.post("/api/finance/goals", payload);
        return data;
    },

    async cancelGoal(id) {
        const { data } = await axios.post(`/api/finance/goals/${id}/cancel`);
        return data;
    },
};
