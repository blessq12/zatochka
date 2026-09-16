import axios from "axios";

export const reviewService = {
    async list(params = {}) {
        const { data } = await axios.get("/api/v1/reviews", { params });
        return data.data;
    },
    async get(id) {
        const { data } = await axios.get(`/api/v1/reviews/${id}`);
        return data.data;
    },
    async publish(id) {
        const { data } = await axios.post(`/api/v1/reviews/${id}/publish`);
        return data.data;
    },
    async reject(id) {
        const { data } = await axios.post(`/api/v1/reviews/${id}/reject`);
        return data.data;
    },
    async hide(id) {
        const { data } = await axios.post(`/api/v1/reviews/${id}/hide`);
        return data.data;
    },
    async reply(id, reply) {
        const { data } = await axios.post(`/api/v1/reviews/${id}/reply`, { reply });
        return data.data;
    },
};
