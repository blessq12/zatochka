import axios from "axios";

const buildPagination = (meta) => {
    if (!meta) {
        return {};
    }

    const total = meta.total ?? 0;
    const currentPage = meta.page ?? 1;
    const perPage = meta.per_page ?? 20;
    const lastPage = Math.max(1, Math.ceil(total / perPage));

    return {
        total,
        current_page: currentPage,
        per_page: perPage,
        last_page: lastPage,
        from: total === 0 ? 0 : (currentPage - 1) * perPage + 1,
        to: Math.min(currentPage * perPage, total),
    };
};

const mapWarehouseItem = (item) => ({
    id: item.id,
    sku: item.materialSku ?? item.sku,
    article: item.materialSku ?? item.sku ?? item.article ?? null,
    name: item.materialName ?? item.name,
    unit: item.unit,
    category:
        typeof item.category === "string"
            ? { name: item.category }
            : item.category,
    quantity: item.quantityOnHand ?? item.quantity,
    quantity_on_hand: item.quantityOnHand ?? item.quantity_on_hand,
});

export const warehouseService = {
    async getAllItems(page = 1, perPage = 20, query = null) {
        const params = { page, per_page: perPage };
        if (query) {
            params.query = query;
        }

        const response = await axios.get("/api/v1/stock-items", { params });

        return {
            items: (response.data.data || []).map(mapWarehouseItem),
            pagination: buildPagination(response.data.meta),
        };
    },

    async getWarehouseItems() {
        const result = await this.getAllItems();
        return result.items;
    },

    async getParts() {
        return this.getWarehouseItems();
    },

    async getMaterials() {
        return this.getWarehouseItems();
    },
};
