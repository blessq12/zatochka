<template>
    <div class="pos-page-content">
        <div class="page-header">
            <h1>Склад</h1>
        </div>

        <!-- Строка поиска -->
        <div class="search-section">
            <input
                v-model="searchQuery"
                type="text"
                class="search-input"
                placeholder="Поиск по названию, артикулу или категории..."
                @input="handleSearch"
            />
        </div>

        <div class="page-body">
            <!-- Результаты -->
            <div v-if="isLoading" class="loading">Загрузка...</div>
            <div v-else-if="items.length === 0" class="empty-state">
                <p v-if="searchQuery">
                    Ничего не найдено по запросу "{{ searchQuery }}"
                </p>
                <p v-else>Товаров на складе нет</p>
            </div>
            <div v-else>
                <div class="warehouse-table-wrapper">
                    <table class="warehouse-table">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Артикул</th>
                                <th>Категория</th>
                                <th>Остаток</th>
                                <th>Цена</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in items"
                                :key="item.id"
                                class="table-row"
                            >
                                <td class="item-name-cell">
                                    <span class="item-name">{{
                                        item.name
                                    }}</span>
                                </td>
                                <td class="item-article-cell">
                                    <span
                                        v-if="item.article"
                                        class="item-article"
                                        >{{ item.article }}</span
                                    >
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td class="item-category-cell">
                                    <span
                                        v-if="item.category"
                                        class="item-category"
                                    >
                                        {{ item.category.name }}
                                    </span>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td class="item-quantity-cell">
                                    <span
                                        :class="{
                                            'quantity-low':
                                                item.quantity <=
                                                    item.min_quantity &&
                                                item.quantity > 0,
                                            'quantity-zero':
                                                item.quantity === 0,
                                        }"
                                    >
                                        {{ formatQuantity(item.quantity) }}
                                        {{ item.unit }}
                                    </span>
                                </td>
                                <td class="item-price-cell">
                                    <span class="item-price"
                                        >{{ formatPrice(item.price) }} ₽</span
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <div
                    v-if="pagination && pagination.last_page > 1"
                    class="pagination-wrapper"
                >
                    <div class="pagination-info">
                        Показано {{ pagination.from }}-{{ pagination.to }} из
                        {{ pagination.total }}
                    </div>
                    <div class="pagination-controls">
                        <button
                            @click="goToPage(pagination.current_page - 1)"
                            :disabled="
                                pagination.current_page === 1 || isLoading
                            "
                            class="pagination-btn"
                        >
                            ← Назад
                        </button>
                        <div class="pagination-pages">
                            <button
                                v-for="page in visiblePages"
                                :key="page"
                                @click="goToPage(page)"
                                :class="[
                                    'pagination-page-btn',
                                    {
                                        active:
                                            page === pagination.current_page,
                                    },
                                ]"
                                :disabled="isLoading || page === '...'"
                            >
                                {{ page }}
                            </button>
                        </div>
                        <button
                            @click="goToPage(pagination.current_page + 1)"
                            :disabled="
                                pagination.current_page ===
                                    pagination.last_page || isLoading
                            "
                            class="pagination-btn"
                        >
                            Вперед →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRoute } from "vue-router";
import { useAutoRefresh } from "../../composables/useAutoRefresh.js";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";
import { warehouseService } from "../../services/pos/WarehouseService.js";

export default {
    name: "WarehousePage",
    setup() {
        const route = useRoute();
        const items = ref([]);
        const pagination = ref(null);
        const isLoading = ref(false);
        const searchQuery = ref("");
        const currentPage = ref(1);
        const perPage = ref(20);
        const { setNavigationItems, reset } = useHeaderNavigation();

        // Вычисляемые страницы для пагинации
        const visiblePages = computed(() => {
            if (!pagination.value) return [];
            const current = pagination.value.current_page;
            const last = pagination.value.last_page;
            const pages = [];

            if (last <= 7) {
                // Если страниц меньше 7, показываем все
                for (let i = 1; i <= last; i++) {
                    pages.push(i);
                }
            } else {
                // Показываем первую, последнюю и текущую с соседями
                if (current <= 3) {
                    for (let i = 1; i <= 4; i++) pages.push(i);
                    pages.push("...");
                    pages.push(last);
                } else if (current >= last - 2) {
                    pages.push(1);
                    pages.push("...");
                    for (let i = last - 3; i <= last; i++) pages.push(i);
                } else {
                    pages.push(1);
                    pages.push("...");
                    for (let i = current - 1; i <= current + 1; i++)
                        pages.push(i);
                    pages.push("...");
                    pages.push(last);
                }
            }
            return pages;
        });

        const fetchItems = async (page = 1, silent = false) => {
            // Показываем индикатор загрузки только при первой загрузке или ручном обновлении
            if (!silent) {
                isLoading.value = true;
            }
            try {
                const search = searchQuery.value.trim() || null;
                const result = await warehouseService.getAllItems(
                    page,
                    perPage.value,
                    search
                );
                // Плавно обновляем данные без моргания
                items.value = result.items;
                pagination.value = result.pagination;
                currentPage.value = page;
            } catch (error) {
                console.error("Error fetching items:", error);
            } finally {
                if (!silent) {
                    isLoading.value = false;
                }
            }
        };

        const goToPage = (page) => {
            if (
                page < 1 ||
                (pagination.value && page > pagination.value.last_page) ||
                page === "..."
            ) {
                return;
            }
            fetchItems(page);
        };

        // Поиск с задержкой (debounce)
        let searchTimeout = null;
        const handleSearch = () => {
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            searchTimeout = setTimeout(() => {
                fetchItems(1);
            }, 500);
        };

        const formatPrice = (price) => {
            if (!price) return "0";
            return parseFloat(price)
                .toFixed(2)
                .replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        };

        const formatQuantity = (quantity) => {
            if (!quantity && quantity !== 0) return "0";
            const qty = parseFloat(quantity);
            return qty % 1 === 0 ? qty.toString() : qty.toFixed(3);
        };

        // Автообновление товаров склада каждые 20 секунд (только если не активен поиск)
        useAutoRefresh(
            (silent) => {
                if (!searchQuery.value.trim()) {
                    fetchItems(currentPage.value, silent);
                }
            },
            20000,
            true
        );

        // Регистрация элементов навигации в Header
        onMounted(() => {
            setNavigationItems([
                {
                    name: "warehouse",
                    label: "Склад",
                    to: { name: "pos.warehouse.index" },
                    active: route.name === "pos.warehouse.index",
                },
            ]);

            fetchItems();
        });

        onUnmounted(() => {
            reset();
        });

        return {
            items,
            pagination,
            isLoading,
            searchQuery,
            visiblePages,
            goToPage,
            handleSearch,
            formatPrice,
            formatQuantity,
        };
    },
};
</script>

<style scoped>
.pos-page-content {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: hidden;
    box-sizing: border-box;
    position: relative;
}

.page-header {
    margin-bottom: 1.5rem;
}

.page-header h1 {
    font-size: 2rem;
    font-weight: 900;
    color: #003859;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.search-section {
    margin-bottom: 2rem;
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.search-input {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    font-family: "Jost", sans-serif;
    transition: border-color 0.2s;
    box-sizing: border-box;
}

.search-input:focus {
    outline: none;
    border-color: #003859;
}

.page-body {
    min-height: 200px;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: hidden;
    box-sizing: border-box;
    position: relative;
}

.loading {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
    font-family: "Jost", sans-serif;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
    font-family: "Jost", sans-serif;
}

.warehouse-table-wrapper {
    overflow-x: auto;
    overflow-y: visible;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #f1f5f9;
    position: relative;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    background: white;
    box-sizing: border-box;
    display: block;
    contain: layout;
}

.warehouse-table-wrapper::-webkit-scrollbar {
    height: 10px;
}

.warehouse-table-wrapper::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 5px;
    margin: 0 4px;
}

.warehouse-table-wrapper::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 5px;
    border: 2px solid #f1f5f9;
}

.warehouse-table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

.warehouse-table {
    width: 100%;
    min-width: 800px;
    border-collapse: collapse;
    font-family: "Jost", sans-serif;
    table-layout: auto;
}

.warehouse-table thead {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.warehouse-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 700;
    color: #003859;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    min-width: 120px;
}

.warehouse-table th:first-child {
    min-width: 200px;
}

.warehouse-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
    font-size: 0.875rem;
    white-space: nowrap;
    min-width: 120px;
}

.warehouse-table td:first-child {
    min-width: 200px;
}

.warehouse-table tbody tr:hover {
    background: #f9fafb;
}

.warehouse-table tbody tr:hover td:first-child {
    background: #f9fafb;
}

.warehouse-table tbody tr:last-child td {
    border-bottom: none;
}

.item-name-cell {
    font-weight: 600;
    color: #003859;
}

.item-name {
    font-size: 0.875rem;
}

.item-article-cell {
    font-family: monospace;
    color: #6b7280;
    font-size: 0.8125rem;
}

.item-category-cell {
    color: #6b7280;
}

.item-category {
    font-size: 0.8125rem;
}

.item-quantity-cell {
    font-weight: 600;
    color: #374151;
}

.quantity-low {
    color: #f59e0b;
}

.quantity-zero {
    color: #ef4444;
}

.item-price-cell {
    font-weight: 700;
    color: #003859;
}

.item-price {
    font-size: 0.875rem;
}

.text-muted {
    color: #9ca3af;
    font-style: italic;
}

.pagination-wrapper {
    margin-top: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-top: 1px solid #e5e7eb;
}

.pagination-info {
    color: #6b7280;
    font-size: 0.875rem;
    font-family: "Jost", sans-serif;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pagination-btn {
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.pagination-btn:hover:not(:disabled) {
    background: #f9fafb;
    border-color: #003859;
    color: #003859;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-pages {
    display: flex;
    gap: 0.25rem;
}

.pagination-page-btn {
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0 0.75rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.pagination-page-btn:hover:not(:disabled) {
    background: #f9fafb;
    border-color: #003859;
}

.pagination-page-btn.active {
    background: #003859;
    color: white;
    border-color: #003859;
}

.pagination-page-btn:disabled {
    cursor: default;
    border: none;
    background: transparent;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .pos-page-content {
        padding: 0.75rem;
        border-radius: 8px;
    }

    .page-header {
        margin-bottom: 0.75rem;
    }

    .page-header h1 {
        font-size: 1.125rem;
    }

    .search-section {
        margin-bottom: 0.75rem;
    }

    .search-input {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }

    .pos-page-content {
        padding: 0.75rem;
        border-radius: 8px;
        overflow-x: hidden;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .page-header,
    .search-section {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .page-body {
        overflow-x: hidden;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .warehouse-table-wrapper {
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        margin: 0;
        padding: 0;
        border-left: none;
        border-right: none;
        border-radius: 0;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .warehouse-table-wrapper::-webkit-scrollbar {
        height: 12px;
    }

    .warehouse-table-wrapper::-webkit-scrollbar-thumb {
        background: #64748b;
        border: 3px solid white;
    }

    .warehouse-table {
        min-width: 700px;
        width: auto;
        font-size: 0.75rem;
    }

    .warehouse-table th,
    .warehouse-table td {
        white-space: nowrap;
        padding: 0.625rem 0.5rem;
    }

    .warehouse-table th:first-child,
    .warehouse-table td:first-child {
        padding-left: 0.75rem;
        min-width: 150px;
    }

    .warehouse-table th {
        font-size: 0.6875rem;
        font-weight: 600;
        padding: 0.75rem 0.5rem;
    }

    .warehouse-table th:not(:first-child) {
        min-width: 100px;
    }

    .warehouse-table td:not(:first-child) {
        min-width: 100px;
    }

    .item-name {
        font-size: 0.75rem;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .item-article-cell,
    .item-category {
        font-size: 0.6875rem;
    }

    .item-price {
        font-size: 0.75rem;
    }

    .pagination-wrapper {
        flex-direction: column;
        gap: 0.75rem;
        align-items: stretch;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
    }

    .pagination-info {
        font-size: 0.75rem;
    }

    .pagination-controls {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.25rem;
    }

    .pagination-btn,
    .pagination-page-btn {
        padding: 0.375rem 0.625rem;
        font-size: 0.75rem;
        min-width: 2rem;
        height: 2rem;
    }
}
</style>
