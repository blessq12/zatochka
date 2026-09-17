import axios from "axios";

export const workshopService = {
    async accept(orderId, orderItemIds) {
        const { data } = await axios.post("/api/workshop/jobs/accept", {
            order_id: orderId,
            order_item_ids: orderItemIds,
        });
        return data;
    },

    async listMine() {
        const { data } = await axios.get("/api/workshop/jobs/mine");
        return data.data || [];
    },

    async get(id) {
        const { data } = await axios.get(`/api/workshop/jobs/${id}`);
        return data;
    },

    async updateItem(jobId, orderItemId, payload) {
        const { data } = await axios.put(
            `/api/workshop/jobs/${jobId}/items/${orderItemId}`,
            payload,
        );
        return data;
    },

    async complete(jobId) {
        const { data } = await axios.post(`/api/workshop/jobs/${jobId}/complete`);
        return data;
    },
};
