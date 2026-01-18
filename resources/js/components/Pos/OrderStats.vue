<template>
    <div class="orders-stats">
        <!-- Десктопная версия -->
        <div class="desktop-stats">
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

        <!-- Мобильная версия с выпадающим меню -->
        <div class="mobile-stats-wrapper">
            <button 
                @click="toggleMobileStats" 
                class="mobile-stats-toggle"
                :class="{ active: isMobileStatsOpen }"
            >
                <span>{{ getActiveLabel() }}</span>
                <span class="mobile-stats-arrow">▼</span>
            </button>
            <div 
                class="mobile-stats-dropdown" 
                :class="{ open: isMobileStatsOpen }"
                v-if="isMobileStatsOpen"
            >
                <router-link
                    :to="{ name: 'pos.orders.new' }"
                    class="mobile-stats-item"
                    :class="{ active: isActive('pos.orders.new') }"
                    @click="closeMobileStats"
                >
                    Новые ({{ ordersCount.new }})
                </router-link>
                <router-link
                    :to="{ name: 'pos.orders.active' }"
                    class="mobile-stats-item"
                    :class="{ active: isActive('pos.orders.active') }"
                    @click="closeMobileStats"
                >
                    Активные ({{ ordersCount.in_work }})
                </router-link>
                <router-link
                    :to="{ name: 'pos.orders.waiting-parts' }"
                    class="mobile-stats-item"
                    :class="{ active: isActive('pos.orders.waiting-parts') }"
                    @click="closeMobileStats"
                >
                    Ожидание запчастей ({{ ordersCount.waiting_parts }})
                </router-link>
                <router-link
                    :to="{ name: 'pos.orders.completed' }"
                    class="mobile-stats-item"
                    :class="{ active: isActive('pos.orders.completed') }"
                    @click="closeMobileStats"
                >
                    Завершенные ({{ ordersCount.ready }})
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, ref, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { usePosStore } from "../../stores/posStore.js";

export default {
    name: "OrderStats",
    setup() {
        const route = useRoute();
        const router = useRouter();
        const posStore = usePosStore();
        const ordersCount = computed(() => posStore.ordersCount);
        const isMobileStatsOpen = ref(false);

        const isActive = (routeName) => {
            return route.name === routeName;
        };

        const getActiveLabel = () => {
            if (isActive('pos.orders.new')) {
                return `Новые (${ordersCount.value.new})`;
            }
            if (isActive('pos.orders.active')) {
                return `Активные (${ordersCount.value.in_work})`;
            }
            if (isActive('pos.orders.waiting-parts')) {
                return `Ожидание запчастей (${ordersCount.value.waiting_parts})`;
            }
            if (isActive('pos.orders.completed')) {
                return `Завершенные (${ordersCount.value.ready})`;
            }
            return 'Статусы заказов';
        };

        const toggleMobileStats = () => {
            isMobileStatsOpen.value = !isMobileStatsOpen.value;
        };

        const closeMobileStats = () => {
            isMobileStatsOpen.value = false;
        };

        // Закрываем меню при клике вне его
        const handleClickOutside = (event) => {
            const target = event.target;
            if (isMobileStatsOpen.value && 
                !target.closest('.mobile-stats-wrapper')) {
                closeMobileStats();
            }
        };

        onMounted(() => {
            document.addEventListener('click', handleClickOutside);
        });

        onUnmounted(() => {
            document.removeEventListener('click', handleClickOutside);
        });

        // Закрываем меню при смене роута
        router.afterEach(() => {
            closeMobileStats();
        });

        return {
            ordersCount,
            isActive,
            isMobileStatsOpen,
            getActiveLabel,
            toggleMobileStats,
            closeMobileStats,
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

.desktop-stats {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.mobile-stats-wrapper {
    display: none;
    position: relative;
    width: 100%;
}

.mobile-stats-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 0.5rem 1rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.mobile-stats-toggle:hover {
    background: #f3f4f6;
    color: #003859;
    border-color: #d1d5db;
}

.mobile-stats-toggle.active {
    background: #003859;
    color: white;
    border-color: #003859;
}

.mobile-stats-arrow {
    font-size: 0.75rem;
    transition: transform 0.2s;
}

.mobile-stats-toggle.active .mobile-stats-arrow {
    transform: rotate(180deg);
}

.mobile-stats-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    overflow: hidden;
    animation: slideDown 0.2s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.mobile-stats-item {
    display: block;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
    border-bottom: 1px solid #f3f4f6;
    font-family: "Jost", sans-serif;
}

.mobile-stats-item:last-child {
    border-bottom: none;
}

.mobile-stats-item:hover {
    background: #f9fafb;
    color: #003859;
}

.mobile-stats-item.active {
    background: #f0f7ff;
    color: #003859;
    font-weight: 600;
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
    white-space: nowrap;
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

/* Мобильная адаптация */
@media (max-width: 768px) {
    .desktop-stats {
        display: none;
    }

    .mobile-stats-wrapper {
        display: block;
    }
}
</style>
