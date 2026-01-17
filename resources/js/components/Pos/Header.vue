<template>
    <div class="pos-header">
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
            <div class="header-navigation" v-if="navigationItems.length > 0">
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
            <div class="header-custom-content" v-if="customContent">
                <component :is="customContent.component" v-bind="customContent.props || {}" :key="customContentKey" />
            </div>
        </div>
        <div class="header-actions">
            <button @click="handleLogout" class="logout-btn">
                <span class="logout-text">Выйти</span>
            </button>
        </div>
    </div>
</template>

<script>
import { computed, resolveComponent, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { usePosStore } from "../../stores/posStore.js";
import { useAutoRefresh } from "../../composables/useAutoRefresh.js";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";

export default {
    name: "PosHeader",
    setup() {
        const router = useRouter();
        const posStore = usePosStore();
        const { navigationItems, customContent } = useHeaderNavigation();

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
            return customContent.value ? JSON.stringify(customContent.value) : null;
        });

        return {
            user,
            fullName,
            userInitials,
            navigationItems,
            customContent,
            customContentKey,
            handleLogout,
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

.header-custom-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
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

@media (max-width: 768px) {
    .pos-header {
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .header-info {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }


    .header-actions {
        width: 100%;
    }

    .logout-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
