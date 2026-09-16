import axios from "axios";

export const documentService = {
    async list() {
        const { data } = await axios.get("/api/v1/legal-documents");
        return data.data;
    },
    async get(slug) {
        const { data } = await axios.get(`/api/v1/legal-documents/${slug}`);
        return data.data;
    },
    async update(slug, payload) {
        const { data } = await axios.put(`/api/v1/legal-documents/${slug}`, payload);
        return data.data;
    },
};
