import axios from "axios";

const TYPES = ["clients", "managers", "masters"];

export const actorService = {
    types: TYPES,

    typeLabel(type) {
        switch (type) {
            case "clients":
                return "Клиенты";
            case "managers":
                return "Менеджеры";
            case "masters":
                return "Мастера";
            default: {
                const _exhaustive = type;
                return String(_exhaustive);
            }
        }
    },

    async list(type) {
        const { data } = await axios.get(`/api/actors/${type}`);
        return data.data || [];
    },

    async get(type, id) {
        const { data } = await axios.get(`/api/actors/${type}/${id}`);
        return data;
    },

    async create(type, payload) {
        const { data } = await axios.post(`/api/actors/${type}`, payload);
        return data;
    },

    async update(type, id, payload) {
        const { data } = await axios.patch(`/api/actors/${type}/${id}`, payload);
        return data;
    },

    async remove(type, id) {
        await axios.delete(`/api/actors/${type}/${id}`);
    },
};
