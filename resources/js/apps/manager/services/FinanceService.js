import axios from "axios";

export const financeService = {
    async listCashOperations(params = {}) {
        const { data } = await axios.get("/api/v1/cash-operations", { params });
        return data.data;
    },
    async getCashOperation(id) {
        const { data } = await axios.get(`/api/v1/cash-operations/${id}`);
        return data.data;
    },
    async createCashOperation(payload) {
        const { data } = await axios.post("/api/v1/cash-operations", payload);
        return data.data;
    },
};
