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
                                    <span class="item-name">{{ item.name }}</span>
                                </td>
                                <td class="item-article-cell">
                                    <span v-if="item.article" class="item-article">{{ item.article }}</span>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td class="item-category-cell">
                                    <span v-if="item.category" class="item-category">
                                        {{ item.category.name }}
                                    </span>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td class="item-quantity-cell">
                                    <span
                                        :class="{
                                            'quantity-low': item.quantity <= item.min_quantity && item.quantity > 0,
                                            'quantity-zero': item.quantity === 0,
                                        }"
                                    >
                                        {{ formatQuantity(item.quantity) }} {{ item.unit }}
                                    </span>
                                </td>
                                <td class="item-price-cell">
                                    <span class="item-price">{{ formatPrice(item.price) }} ₽</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <div v-if="pagination && pagination.last_page > 1" class="pagination-wrapper">
                    <div class="pagination-info">
                        Показано {{ pagination.from }}-{{ pagination.to }} из {{ pagination.total }}
                    </div>
                    <div class="pagination-controls">
                        <button
                            @click="goToPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1 || isLoading"
                            class="pagination-btn"
                        >
                            ← Назад
                        </button>
                        <div class="pagination-pages">
                            <button
                                v-for="page in visiblePages"
                                :key="page"
                                @click="goToPage(page)"
                                :class="['pagination-page-btn', { active: page === pagination.current_page }]"
                                :disabled="isLoading || page === '...'"
                            >
                                {{ page }}
                            </button>
                        </div>
                        <button
                            @click="goToPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page || isLoading"
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
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import { warehouseService } from "../../services/pos/WarehouseService.js";
import { useAutoRefresh } from "../../composables/useAutoRefresh.js";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";

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
                    pages.push('...');
                    pages.push(last);
                } else if (current >= last - 2) {
                    pages.push(1);
                    pages.push('...');
                    for (let i = last - 3; i <= last; i++) pages.push(i);
                } else {
                    pages.push(1);
                    pages.push('...');
                    for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                    pages.push('...');
                    pages.push(last);
                }
            }
            return pages;
        });

        const fetchItems = async (page = 1) => {
            isLoading.value = true;
            try {
                const search = searchQuery.value.trim() || null;
                const result = await warehouseService.getAllItems(page, perPage.value, search);
                items.value = result.items;
                pagination.value = result.pagination;
                currentPage.value = page;
            } catch (error) {
                console.error("Error fetching items:", error);
            } finally {
                isLoading.value = false;
            }
        };

        const goToPage = (page) => {
            if (page < 1 || (pagination.value && page > pagination.value.last_page) || page === '...') {
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
        useAutoRefresh(() => {
            if (!searchQuery.value.trim()) {
                fetchItems(currentPage.value);
            }
        }, 20000, true);

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
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    font-family: "Jost", sans-serif;
    transition: border-color 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #003859;
}

.page-body {
    min-height: 200px;
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
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.warehouse-table {
    width: 100%;
    border-collapse: collapse;
    font-family: "Jost", sans-serif;
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
}

.warehouse-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
    font-size: 0.875rem;
}

.warehouse-table tbody tr:hover {
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
</style>
