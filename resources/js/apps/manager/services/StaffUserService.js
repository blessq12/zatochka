import axios from "axios";

export const staffUserService = {
    async list(params = {}) {
        const { data } = await axios.get("/api/v1/staff-users", { params });
        return data.data;
    },
    async get(id) {
        const { data } = await axios.get(`/api/v1/staff-users/${id}`);
        return data.data;
    },
    async create(payload) {
        const { data } = await axios.post("/api/v1/staff-users", payload);
        return data.data;
    },
    async update(id, payload) {
        const { data } = await axios.patch(`/api/v1/staff-users/${id}`, payload);
        return data.data;
    },
    async changePassword(id, password) {
        const { data } = await axios.post(`/api/v1/staff-users/${id}/password`, { password });
        return data.data;
    },
    async remove(id) {
        await axios.delete(`/api/v1/staff-users/${id}`);
    },
};
