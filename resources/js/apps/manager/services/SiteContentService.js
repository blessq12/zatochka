import axios from "axios";

export const siteContentService = {
    async get() {
        const { data } = await axios.get("/api/site-content");
        return data;
    },

    async updateSection(section, payload) {
        const { data } = await axios.put(`/api/site-content/${section}`, payload);
        return data.data;
    },

    async saveLegal(payload) {
        const { data } = await axios.put("/api/site-content/legal", payload);
        return data.data;
    },

    async deleteLegal(slug) {
        await axios.delete(`/api/site-content/legal/${slug}`);
    },
};
