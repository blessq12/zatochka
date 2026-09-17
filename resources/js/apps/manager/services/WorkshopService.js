import axios from "axios";

export const workshopService = {
    async getByOrder(orderId) {
        const { data } = await axios.get(
            `/api/workshop/jobs/by-order/${orderId}`,
        );
        return data;
    },
};
