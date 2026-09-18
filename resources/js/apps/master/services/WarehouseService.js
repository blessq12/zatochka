import axios from "axios";

const CATEGORY_LABELS = {
    spare_part: "Запчасти",
    consumable: "Расходники",
};

export const warehouseService = {
    categoryLabel(category) {
        return CATEGORY_LABELS[category] || category;
    },

    async list(category = null) {
        const params = {};
        if (category) params.category = category;
        const { data } = await axios.get("/api/warehouse/items", { params });
        return data.data || [];
    },
};
