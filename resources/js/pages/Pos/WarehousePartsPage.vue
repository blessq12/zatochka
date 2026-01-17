<template>
    <div class="pos-page-content">
        <div class="page-header">
            <h1>Запчасти</h1>
        </div>

        <!-- Строка поиска -->
        <div class="search-section">
            <input
                v-model="searchQuery"
                type="text"
                class="search-input"
                placeholder="Поиск по названию или артикулу..."
            />
        </div>

        <div class="page-body">
            <!-- Результаты -->
            <div v-if="isLoading" class="loading">Загрузка...</div>
            <div v-else-if="filteredItems.length === 0" class="empty-state">
                <p v-if="searchQuery">
                    Ничего не найдено по запросу "{{ searchQuery }}"
                </p>
                <p v-else>Запчастей нет</p>
            </div>
            <div v-else class="items-list">
                <div
                    v-for="item in filteredItems"
                    :key="item.id"
                    class="item-card"
                >
                    <div class="item-header">
                        <span class="item-name">{{ item.name }}</span>
                        <span class="item-quantity"
                            >{{ item.quantity }} {{ item.unit }}</span
                        >
                    </div>
                    <div class="item-info">
                        <p v-if="item.sku">
                            <strong>Артикул:</strong> {{ item.sku }}
                        </p>
                        <p v-if="item.retail_price">
                            <strong>Цена:</strong>
                            {{ formatPrice(item.retail_price) }} ₽
                        </p>
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
import { orderService } from "../../services/pos/OrderService.js";
import { useAutoRefresh } from "../../composables/useAutoRefresh.js";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";

export default {
    name: "WarehousePartsPage",
    setup() {
        const route = useRoute();
        const items = ref([]);
        const isLoading = ref(false);
        const searchQuery = ref("");
        const { setNavigationItems, reset } = useHeaderNavigation();

        // Фильтрация товаров по поисковому запросу
        const filteredItems = computed(() => {
            if (!searchQuery.value.trim()) {
                return items.value;
            }

            const query = searchQuery.value.trim().toLowerCase();
            return items.value.filter((item) => {
                const nameMatch = item.name?.toLowerCase().includes(query);
                const articleMatch = item.article
                    ?.toLowerCase()
                    .includes(query);
                return nameMatch || articleMatch;
            });
        });

        const fetchItems = async () => {
            isLoading.value = true;
            try {
                items.value = await warehouseService.getParts();
            } catch (error) {
                console.error("Error fetching items:", error);
            } finally {
                isLoading.value = false;
            }
        };

        // Автообновление товаров склада каждые 20 секунд
        useAutoRefresh(fetchItems, 20000, true);

        // Регистрация элементов навигации в Header
        onMounted(() => {
            setNavigationItems([
                {
                    name: "parts",
                    label: "Запчасти",
                    to: { name: "pos.warehouse.parts" },
                    active: route.name === "pos.warehouse.parts",
                },
                {
                    name: "materials",
                    label: "Расходные материалы",
                    to: { name: "pos.warehouse.materials" },
                    active: route.name === "pos.warehouse.materials",
                },
            ]);
        });

        onUnmounted(() => {
            reset();
        });

        return {
            items,
            isLoading,
            searchQuery,
            filteredItems,
            formatPrice: orderService.formatPrice,
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
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #046490;
    box-shadow: 0 0 0 3px rgba(4, 100, 144, 0.1);
}

.loading,
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.items-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.item-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.2s;
}

.item-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
}

.item-name {
    font-weight: 700;
    font-size: 1.125rem;
    color: #003859;
}

.item-quantity {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    background: #dbeafe;
    color: #1e40af;
}

.item-info p {
    margin: 0.5rem 0;
    color: #374151;
    font-size: 0.875rem;
}
</style>
