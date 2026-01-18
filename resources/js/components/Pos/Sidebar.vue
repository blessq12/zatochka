<script>
import { usePosStore } from "../../stores/posStore.js";
import { useRoute } from "vue-router";

export default {
    name: "PosSidebar",
    props: {
        isMobileOpen: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['close'],
    setup() {
        const route = useRoute();

        const isActiveSection = (sectionName) => {
            const routeName = route.name || "";
            return routeName.startsWith(`pos.${sectionName}`);
        };

        return {
            isActiveSection,
        };
    },
    computed: {
        posStore() {
            return usePosStore();
        },
        fullName() {
            const user = this.posStore.user;
            if (!user) return "";
            const parts = [user.surname, user.name].filter(Boolean);
            return parts.length > 0 ? parts.join(" ") : user.name || "";
        },
    },
};
</script>

<template>
    <div class="pos-sidebar" :class="{ 'mobile-open': isMobileOpen }">
        <div class="sidebar-header">
            <div class="sidebar-header-content">
                <h2 class="sidebar-title">Заточка.ТСК</h2>
                <p class="sidebar-subtitle">POS Панель</p>
            </div>
            <button v-if="isMobileOpen" @click="$emit('close')" class="mobile-close-btn">
                ✕
            </button>
        </div>

        <nav class="sidebar-nav">
            <!-- Заказы -->
            <router-link
                :to="{ name: 'pos.orders.new' }"
                class="nav-section-header"
                :class="{ active: isActiveSection('orders') }"
            >
                <span class="nav-icon">📋</span>
                <span class="nav-title">Заказы</span>
            </router-link>

            <!-- Склад -->
            <router-link
                :to="{ name: 'pos.warehouse.index' }"
                class="nav-section-header"
                :class="{ active: isActiveSection('warehouse') }"
            >
                <span class="nav-icon">📦</span>
                <span class="nav-title">Склад</span>
            </router-link>

            <!-- Настройки -->
            <router-link
                :to="{ name: 'pos.settings.profile' }"
                class="nav-section-header"
                :class="{ active: isActiveSection('settings') }"
            >
                <span class="nav-icon">⚙️</span>
                <span class="nav-title">Настройки</span>
            </router-link>
        </nav>
    </div>
</template>

<style scoped>
.pos-sidebar {
    width: 280px;
    background: linear-gradient(180deg, #003859 0%, #002c4e 100%);
    color: white;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    font-family: "Jost", sans-serif;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    z-index: 999;
    transition: transform 0.3s ease;
}

.sidebar-header {
    padding: 2rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.sidebar-header-content {
    flex: 1;
}

.mobile-close-btn {
    display: none;
    background: transparent;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.25rem;
    line-height: 1;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    transition: background 0.2s;
}

.mobile-close-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar-title {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 900;
    font-family: "Jost", sans-serif;
    color: white;
}

.sidebar-subtitle {
    margin: 0 0 12px 0;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.user-name {
    margin: 0;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 400;
}

.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
}

.nav-section-header {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    cursor: pointer;
    transition: background 0.2s;
    user-select: none;
    font-weight: 500;
    text-decoration: none;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 0.5rem;
}

.nav-section-header:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.nav-section-header.active {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-left: 3px solid #c3006b;
}

.nav-icon {
    font-size: 1.25rem;
    margin-right: 0.75rem;
    width: 24px;
    text-align: center;
}

.nav-title {
    flex: 1;
    font-weight: 500;
    font-size: 1rem;
    font-family: "Jost", sans-serif;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .pos-sidebar {
        transform: translateX(-100%);
        width: 280px;
    }

    .pos-sidebar.mobile-open {
        transform: translateX(0);
    }

    .mobile-close-btn {
        display: block;
    }

    .sidebar-header {
        padding: 1.5rem 1rem;
    }

    .sidebar-title {
        font-size: 1.25rem;
    }

    .sidebar-subtitle {
        font-size: 0.75rem;
    }
}
</style>
