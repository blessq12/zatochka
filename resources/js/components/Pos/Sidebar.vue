<script>
export default {
    name: "PosSidebar",
    data() {
        return {
            activeSection: "orders",
            sections: {
                orders: true,
                warehouse: false,
                settings: false,
            },
        };
    },
    methods: {
        toggleSection(section) {
            this.sections[section] = !this.sections[section];
        },
        setActiveSection(section) {
            this.activeSection = section;
        },
    },
};
</script>

<template>
    <div class="pos-sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Заточка.ТСК</h2>
            <p class="sidebar-subtitle">POS Панель</p>
        </div>

        <nav class="sidebar-nav">
            <!-- Заказы -->
            <div class="nav-section">
                <div
                    class="nav-section-header"
                    @click="toggleSection('orders')"
                >
                    <span class="nav-icon">📋</span>
                    <span class="nav-title">Заказы</span>
                    <span
                        class="nav-arrow"
                        :class="{ open: sections.orders }"
                    >
                        ▼
                    </span>
                </div>
                <div v-show="sections.orders" class="nav-submenu">
                    <router-link
                        :to="{ name: 'pos.orders.new' }"
                        class="nav-item"
                        active-class="active"
                        @click="setActiveSection('orders')"
                    >
                        Новые
                    </router-link>
                    <router-link
                        :to="{ name: 'pos.orders.active' }"
                        class="nav-item"
                        active-class="active"
                        @click="setActiveSection('orders')"
                    >
                        Активные
                    </router-link>
                    <router-link
                        :to="{ name: 'pos.orders.completed' }"
                        class="nav-item"
                        active-class="active"
                        @click="setActiveSection('orders')"
                    >
                        Завершенные
                    </router-link>
                </div>
            </div>

            <!-- Склад -->
            <div class="nav-section">
                <div
                    class="nav-section-header"
                    @click="toggleSection('warehouse')"
                >
                    <span class="nav-icon">📦</span>
                    <span class="nav-title">Склад</span>
                    <span
                        class="nav-arrow"
                        :class="{ open: sections.warehouse }"
                    >
                        ▼
                    </span>
                </div>
                <div v-show="sections.warehouse" class="nav-submenu">
                    <router-link
                        :to="{ name: 'pos.warehouse.parts' }"
                        class="nav-item"
                        active-class="active"
                        @click="setActiveSection('warehouse')"
                    >
                        Запчасти
                    </router-link>
                    <router-link
                        :to="{ name: 'pos.warehouse.materials' }"
                        class="nav-item"
                        active-class="active"
                        @click="setActiveSection('warehouse')"
                    >
                        Расходные материалы
                    </router-link>
                </div>
            </div>

            <!-- Настройки -->
            <div class="nav-section">
                <div
                    class="nav-section-header"
                    @click="toggleSection('settings')"
                >
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-title">Настройки</span>
                    <span
                        class="nav-arrow"
                        :class="{ open: sections.settings }"
                    >
                        ▼
                    </span>
                </div>
                <div v-show="sections.settings" class="nav-submenu">
                    <router-link
                        :to="{ name: 'pos.settings.profile' }"
                        class="nav-item"
                        active-class="active"
                        @click="setActiveSection('settings')"
                    >
                        Профиль
                    </router-link>
                </div>
            </div>
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
}

.sidebar-header {
    padding: 2rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.1);
}

.sidebar-title {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 900;
    font-family: "Jost", sans-serif;
    color: white;
}

.sidebar-subtitle {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
}

.nav-section {
    margin-bottom: 0.5rem;
}

.nav-section-header {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    cursor: pointer;
    transition: background 0.2s;
    user-select: none;
    font-weight: 500;
}

.nav-section-header:hover {
    background: rgba(255, 255, 255, 0.1);
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

.nav-arrow {
    font-size: 0.75rem;
    transition: transform 0.2s;
    color: rgba(255, 255, 255, 0.6);
}

.nav-arrow.open {
    transform: rotate(180deg);
}

.nav-submenu {
    background: rgba(0, 0, 0, 0.2);
    padding: 0.5rem 0;
}

.nav-item {
    display: block;
    padding: 0.75rem 1.5rem 0.75rem 3.5rem;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
    border-left: 3px solid transparent;
    font-weight: 400;
    font-family: "Jost", sans-serif;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.nav-item.active {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-left-color: #c3006b;
    font-weight: 500;
}
</style>
