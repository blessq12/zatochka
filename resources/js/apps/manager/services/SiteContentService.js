import axios from "axios";

export const siteContentService = {
    async get() {
        const { data } = await axios.get("/api/v1/site-content");
        return data.data;
    },
    async save(payload) {
        const { data } = await axios.put("/api/v1/site-content", payload);
        return data.data;
    },
};
