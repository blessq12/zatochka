import axios from "axios";

export const dashboardService = {
    async get() {
        const { data } = await axios.get("/api/manager/dashboard");
        return data;
    },
};
