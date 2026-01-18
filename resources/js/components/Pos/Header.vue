<template>
    <div class="pos-header">
        <button @click="$emit('toggle-mobile-menu')" class="mobile-menu-btn">
            ☰
        </button>
        <div class="header-info">
            <div class="user-info">
                <div class="user-avatar">
                    {{ userInitials }}
                </div>
                <div class="user-details">
                    <div class="user-name">{{ fullName }}</div>
                    <div class="user-email">{{ user?.email }}</div>
                </div>
            </div>
            
            <!-- Десктопная навигация -->
            <div class="header-navigation desktop-nav" v-if="navigationItems.length > 0">
                <router-link
                    v-for="item in navigationItems"
                    :key="item.name"
                    :to="item.to"
                    class="nav-btn"
                    :class="{ active: item.active }"
                >
                    {{ item.label }}
                </router-link>
            </div>
            
            <!-- Десктопный кастомный контент (статусы заказов) -->
            <div class="header-custom-content desktop-custom" v-if="customContent">
                <component
                    :is="customContent.component"
                    v-bind="customContent.props || {}"
                    :key="customContentKey"
                />
            </div>
        </div>
        
        <!-- Десктопная кнопка выхода -->
        <div class="header-actions desktop-actions">
            <button @click="handleLogout" class="logout-btn">
                <span class="logout-text">Выйти</span>
            </button>
        </div>

        <!-- Мобильное универсальное меню -->
        <div class="mobile-unified-menu">
            <button 
                @click="toggleMobileMenu" 
                class="mobile-menu-toggle"
                :class="{ active: isMobileMenuOpen }"
            >
                <span>Меню</span>
                <span class="mobile-menu-arrow">▼</span>
            </button>
            <div 
                class="mobile-menu-dropdown" 
                :class="{ open: isMobileMenuOpen }"
                v-if="isMobileMenuOpen"
            >
                <!-- Навигация -->
                <div v-if="navigationItems.length > 0" class="mobile-menu-section">
                    <div class="mobile-menu-section-title">Навигация</div>
                    <router-link
                        v-for="item in navigationItems"
                        :key="item.name"
                        :to="item.to"
                        class="mobile-menu-item"
                        :class="{ active: item.active }"
                        @click="closeMobileMenu"
                    >
                        {{ item.label }}
                    </router-link>
                </div>

                <!-- Статусы заказов -->
                <div v-if="customContent && orderStatsItems.length > 0" class="mobile-menu-section">
                    <div class="mobile-menu-section-title">Статусы заказов</div>
                    <router-link
                        v-for="item in orderStatsItems"
                        :key="item.name"
                        :to="item.to"
                        class="mobile-menu-item"
                        :class="{ active: item.active }"
                        @click="closeMobileMenu"
                    >
                        {{ item.label }}
                    </router-link>
                </div>

                <!-- Кнопка выхода -->
                <div class="mobile-menu-section">
                    <button @click="handleMobileLogout" class="mobile-menu-logout">
                        Выйти
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, ref, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAutoRefresh } from "../../composables/useAutoRefresh.js";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";
import { usePosStore } from "../../stores/posStore.js";

export default {
    name: "PosHeader",
    emits: ['toggle-mobile-menu'],
    setup() {
        const router = useRouter();
        const route = useRoute();
        const posStore = usePosStore();
        const { navigationItems, customContent } = useHeaderNavigation();
        const isMobileMenuOpen = ref(false);
        const ordersCount = computed(() => posStore.ordersCount);

        const user = computed(() => posStore.user);

        const fullName = computed(() => {
            if (!user.value) return "";
            const parts = [user.value.surname, user.value.name].filter(Boolean);
            return parts.length > 0 ? parts.join(" ") : user.value.name || "";
        });

        const userInitials = computed(() => {
            if (!user.value) return "M";
            const name = user.value.name || "";
            const surname = user.value.surname || "";
            const firstInitial = name.charAt(0).toUpperCase();
            const secondInitial = surname.charAt(0).toUpperCase();
            return secondInitial
                ? `${firstInitial}${secondInitial}`
                : firstInitial || "M";
        });

        const handleLogout = async () => {
            await posStore.logout();
            router.push("/pos");
        };

        // Автообновление счетчиков заказов каждые 20 секунд
        useAutoRefresh(() => posStore.getOrdersCount(), 20000, true);

        // Ключ для принудительного обновления компонента
        const customContentKey = computed(() => {
            return customContent.value
                ? JSON.stringify(customContent.value)
                : null;
        });

        // Формируем список статусов заказов для мобильного меню
        const orderStatsItems = computed(() => {
            if (!customContent.value) return [];
            
            return [
                {
                    name: 'pos.orders.new',
                    to: { name: 'pos.orders.new' },
                    label: `Новые (${ordersCount.value.new || 0})`,
                    active: route.name === 'pos.orders.new'
                },
                {
                    name: 'pos.orders.active',
                    to: { name: 'pos.orders.active' },
                    label: `Активные (${ordersCount.value.in_work || 0})`,
                    active: route.name === 'pos.orders.active'
                },
                {
                    name: 'pos.orders.waiting-parts',
                    to: { name: 'pos.orders.waiting-parts' },
                    label: `Ожидание запчастей (${ordersCount.value.waiting_parts || 0})`,
                    active: route.name === 'pos.orders.waiting-parts'
                },
                {
                    name: 'pos.orders.completed',
                    to: { name: 'pos.orders.completed' },
                    label: `Завершенные (${ordersCount.value.ready || 0})`,
                    active: route.name === 'pos.orders.completed'
                }
            ];
        });

        const toggleMobileMenu = () => {
            isMobileMenuOpen.value = !isMobileMenuOpen.value;
        };

        const closeMobileMenu = () => {
            isMobileMenuOpen.value = false;
        };

        const handleMobileLogout = async () => {
            closeMobileMenu();
            await handleLogout();
        };

        // Закрываем меню при клике вне его
        const handleClickOutside = (event) => {
            const target = event.target;
            if (isMobileMenuOpen.value && 
                !target.closest('.mobile-unified-menu')) {
                closeMobileMenu();
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
            closeMobileMenu();
        });

        return {
            user,
            fullName,
            userInitials,
            navigationItems,
            customContent,
            customContentKey,
            handleLogout,
            isMobileMenuOpen,
            orderStatsItems,
            toggleMobileMenu,
            closeMobileMenu,
            handleMobileLogout,
        };
    },
};
</script>

<style scoped>
.pos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 0.75rem 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
    border-radius: 12px;
    font-family: "Jost", sans-serif;
    gap: 1rem;
}

.header-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 2rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-navigation {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.mobile-menu-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 0.75rem 1rem;
    background: #003859;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.mobile-menu-toggle:hover {
    background: #002c4e;
}

.mobile-menu-toggle.active {
    background: #002c4e;
}

.mobile-menu-arrow {
    font-size: 0.75rem;
    transition: transform 0.2s;
    margin-left: 0.5rem;
}

.mobile-menu-toggle.active .mobile-menu-arrow {
    transform: rotate(180deg);
}

.mobile-menu-dropdown {
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
    max-height: 80vh;
    overflow-y: auto;
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

.mobile-menu-section {
    border-bottom: 1px solid #f3f4f6;
}

.mobile-menu-section:last-child {
    border-bottom: none;
}

.mobile-menu-section-title {
    padding: 0.75rem 1rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-family: "Jost", sans-serif;
}

.mobile-menu-item {
    display: block;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
    border-bottom: 1px solid #f9fafb;
    font-family: "Jost", sans-serif;
}

.mobile-menu-item:last-child {
    border-bottom: none;
}

.mobile-menu-item:hover {
    background: #f9fafb;
    color: #003859;
}

.mobile-menu-item.active {
    background: #f0f7ff;
    color: #003859;
    font-weight: 600;
    border-left: 3px solid #003859;
}

.mobile-menu-logout {
    width: 100%;
    padding: 0.75rem 1rem;
    background: #ef4444;
    border: none;
    border-radius: 0;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
    text-align: left;
}

.mobile-menu-logout:hover {
    background: #dc2626;
}

.header-custom-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.mobile-unified-menu {
    display: none;
    position: relative;
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

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #003859 0%, #c3006b 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}

.user-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.user-name {
    font-weight: 600;
    font-size: 0.9375rem;
    color: #003859;
}

.user-email {
    font-size: 0.8125rem;
    color: #6b7280;
}

.header-actions {
    display: flex;
    align-items: center;
}

.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.logout-btn:hover {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
}

.logout-text {
    font-size: 0.875rem;
}

.mobile-menu-btn {
    display: none;
    background: #003859;
    color: white;
    border: none;
    padding: 0.5rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1.25rem;
    width: 40px;
    height: 40px;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    flex-shrink: 0;
}

.mobile-menu-btn:hover {
    background: #002c4e;
}

@media (max-width: 768px) {
    .mobile-menu-btn {
        display: flex;
        width: 36px;
        height: 36px;
        font-size: 1.125rem;
    }

    .pos-header {
        padding: 0.625rem 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 0.75rem;
        border-radius: 8px;
    }

    .header-info {
        flex: 1;
        min-width: 0;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .user-info {
        min-width: 0;
        flex-shrink: 0;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        font-size: 0.875rem;
    }

    .user-details {
        min-width: 0;
    }

    .user-name {
        font-size: 0.8125rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-email {
        font-size: 0.6875rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Скрываем десктопные элементы на мобильных */
    .desktop-nav,
    .desktop-custom,
    .desktop-actions {
        display: none;
    }

    /* Показываем универсальное мобильное меню */
    .mobile-unified-menu {
        display: block;
        width: 100%;
        order: 3;
        margin-top: 0.5rem;
    }

    .mobile-menu-toggle {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }

    .mobile-menu-dropdown {
        top: calc(100% + 0.375rem);
    }

    .mobile-menu-section-title {
        padding: 0.625rem 0.75rem 0.375rem;
        font-size: 0.6875rem;
    }

    .mobile-menu-item {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }

    .mobile-menu-logout {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }
}
</style>
