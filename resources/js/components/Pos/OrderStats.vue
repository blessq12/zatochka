<template>
    <div class="orders-stats">
        <router-link
            :to="{ name: 'pos.orders.new' }"
            class="nav-btn"
            :class="{ active: isActive('pos.orders.new') }"
        >
            Новые ({{ ordersCount.new }})
        </router-link>
        <router-link
            :to="{ name: 'pos.orders.active' }"
            class="nav-btn"
            :class="{ active: isActive('pos.orders.active') }"
        >
            Активные ({{ ordersCount.in_work }})
        </router-link>
        <router-link
            :to="{ name: 'pos.orders.waiting-parts' }"
            class="nav-btn"
            :class="{ active: isActive('pos.orders.waiting-parts') }"
        >
            Ожидание запчастей ({{ ordersCount.waiting_parts }})
        </router-link>
        <router-link
            :to="{ name: 'pos.orders.completed' }"
            class="nav-btn"
            :class="{ active: isActive('pos.orders.completed') }"
        >
            Завершенные ({{ ordersCount.ready }})
        </router-link>
    </div>
</template>

<script>
import { computed } from "vue";
import { useRoute } from "vue-router";
import { usePosStore } from "../../stores/posStore.js";

export default {
    name: "OrderStats",
    setup() {
        const route = useRoute();
        const posStore = usePosStore();
        const ordersCount = computed(() => posStore.ordersCount);

        const isActive = (routeName) => {
            return route.name === routeName;
        };

        return {
            ordersCount,
            isActive,
        };
    },
};
</script>

<style scoped>
.orders-stats {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.nav-btn {
    padding: 0.5rem 1rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.nav-btn:hover {
    background: #f3f4f6;
    color: #003859;
    border-color: #d1d5db;
}

.nav-btn.active {
    background: #003859;
    color: white;
    border-color: #003859;
}
</style>
