import axios from "axios";

/**
 * Сервис для работы со складом в POS панели мастера
 */
export const warehouseService = {
    /**
     * Получить товары склада с фильтрацией по типу
     * @param {string|null} type - Тип товара: 'parts', 'materials' или null для всех
     * @returns {Promise<Array>} Массив товаров склада
     */
    async getWarehouseItems(type = null) {
        try {
            const params = type ? { type } : {};
            const response = await axios.get("/api/pos/warehouse/items", {
                params,
            });
            return response.data.items || [];
        } catch (error) {
            console.error("Error fetching warehouse items:", error);
            throw error;
        }
    },

    /**
     * Получить запчасти
     * @returns {Promise<Array>}
     */
    async getParts() {
        return this.getWarehouseItems("parts");
    },

    /**
     * Получить расходные материалы
     * @returns {Promise<Array>}
     */
    async getMaterials() {
        return this.getWarehouseItems("materials");
    },
};
