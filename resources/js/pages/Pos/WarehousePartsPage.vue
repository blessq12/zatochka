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

        const fetchItems = async (silent = false) => {
            // Показываем индикатор загрузки только при первой загрузке или ручном обновлении
            if (!silent) {
                isLoading.value = true;
            }
            try {
                const newItems = await warehouseService.getParts();
                // Плавно обновляем список без моргания
                items.value = newItems;
            } catch (error) {
                console.error("Error fetching items:", error);
            } finally {
                if (!silent) {
                    isLoading.value = false;
                }
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
        };
    },
};
</script>

<style scoped>
.pos-page-content {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    font-family: "Jost", sans-serif;
}

.page-header {
    margin-bottom: 1.5rem;
}

.page-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #003859;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.search-section {
    margin-bottom: 1.5rem;
    display: block;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid rgba(0, 56, 89, 0.25);
    border-radius: 0;
    font-size: 1rem;
    font-family: "Jost", sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #003859;
    box-shadow: 0 0 0 3px rgba(0, 56, 89, 0.15);
}

.loading,
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
    font-weight: 500;
}

.items-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
}

@media (max-width: 768px) {
    .pos-page-content {
        padding: 0.75rem;
        border-radius: 0;
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

    .loading,
    .empty-state {
        padding: 2rem 1rem;
        font-size: 0.875rem;
    }

    .items-list {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .item-card {
        padding: 0.75rem;
        border-radius: 0;
    }

    .item-name {
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .item-details {
        font-size: 0.75rem;
        gap: 0.375rem;
    }

    .item-detail-label {
        font-size: 0.6875rem;
    }

    .item-detail-value {
        font-size: 0.75rem;
    }
}

.item-card {
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
    padding: 1.5rem;
    transition: all 0.2s;
    backdrop-filter: blur(8px);
}

.item-card:hover {
    box-shadow: 0 4px 16px rgba(0, 56, 89, 0.12);
    transform: translateY(-2px);
    border-color: rgba(0, 56, 89, 0.35);
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
    border-radius: 0;
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
