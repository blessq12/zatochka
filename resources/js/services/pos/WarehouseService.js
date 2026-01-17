import axios from "axios";

/**
 * Сервис для работы со складом в POS панели мастера
 */
export const warehouseService = {
    /**
     * Получить товары склада с пагинацией
     * @param {number} page - Номер страницы
     * @param {number} perPage - Количество элементов на странице
     * @param {string} search - Поисковый запрос
     * @returns {Promise<Object>} Объект с items и pagination
     */
    async getAllItems(page = 1, perPage = 20, search = null) {
        try {
            const params = {
                page,
                per_page: perPage,
            };
            if (search) {
                params.search = search;
            }
            const response = await axios.get("/api/pos/warehouse/items", { params });
            return {
                items: response.data.items || [],
                pagination: response.data.pagination || {},
            };
        } catch (error) {
            console.error("Error fetching warehouse items:", error);
            throw error;
        }
    },

    /**
     * Получить товары склада с фильтрацией по типу (для обратной совместимости)
     * @param {string|null} type - Тип товара: 'parts', 'materials' или null для всех
     * @returns {Promise<Array>} Массив товаров склада
     */
    async getWarehouseItems(type = null) {
        // Теперь всегда возвращаем все товары, фильтрация по типу не используется
        const result = await this.getAllItems();
        return result.items;
    },

    /**
     * Получить запчасти (для обратной совместимости)
     * @returns {Promise<Array>}
     */
    async getParts() {
        const result = await this.getAllItems();
        return result.items;
    },

    /**
     * Получить расходные материалы (для обратной совместимости)
     * @returns {Promise<Array>}
     */
    async getMaterials() {
        const result = await this.getAllItems();
        return result.items;
    },
};
