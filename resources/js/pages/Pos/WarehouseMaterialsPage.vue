<template>
    <div class="pos-page-content">
        <div class="page-header">
            <h1>Расходные материалы</h1>
        </div>
        <div class="page-body">
            <div v-if="isLoading" class="loading">Загрузка...</div>
            <div v-else-if="items.length === 0" class="empty-state">
                <p>Расходных материалов нет</p>
            </div>
            <div v-else class="items-list">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="item-card"
                >
                    <div class="item-header">
                        <span class="item-name">{{ item.name }}</span>
                        <span class="item-quantity">{{ item.quantity }} {{ item.unit }}</span>
                    </div>
                    <div class="item-info">
                        <p v-if="item.sku"><strong>Артикул:</strong> {{ item.sku }}</p>
                        <p v-if="item.retail_price">
                            <strong>Цена:</strong> {{ formatPrice(item.retail_price) }} ₽
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { warehouseService } from "../../services/pos/WarehouseService.js";
import { orderService } from "../../services/pos/OrderService.js";

export default {
    name: "WarehouseMaterialsPage",
    setup() {
        const items = ref([]);
        const isLoading = ref(false);

        const fetchItems = async () => {
            isLoading.value = true;
            try {
                items.value = await warehouseService.getMaterials();
            } catch (error) {
                console.error("Error fetching items:", error);
            } finally {
                isLoading.value = false;
            }
        };

        onMounted(() => {
            fetchItems();
        });

        return {
            items,
            isLoading,
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

.page-header h1 {
    font-size: 2rem;
    font-weight: 900;
    color: #003859;
    margin: 0 0 2rem 0;
    font-family: "Jost", sans-serif;
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
