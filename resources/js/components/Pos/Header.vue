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
            <div class="orders-stats">
                <router-link
                    :to="{ name: 'pos.orders.new' }"
                    class="stat-item stat-item-new"
                >
                    <span class="stat-label">Новых</span>
                    <span class="stat-value">{{ ordersCount.new }}</span>
                </router-link>
                <router-link
                    :to="{ name: 'pos.orders.active' }"
                    class="stat-item stat-item-active"
                >
                    <span class="stat-label">В работе</span>
                    <span class="stat-value">{{ ordersCount.in_work }}</span>
                </router-link>
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
import { computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { usePosStore } from "../../stores/posStore.js";

export default {
    name: "PosHeader",
    setup() {
        const router = useRouter();
        const posStore = usePosStore();

        const user = computed(() => posStore.user);
        const ordersCount = computed(() => posStore.ordersCount);

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

        onMounted(() => {
            // Загружаем счетчики заказов при монтировании
            posStore.getOrdersCount();
        });

        return {
            user,
            fullName,
            userInitials,
            ordersCount,
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

.orders-stats {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.stat-item {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f9fafb;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.stat-item-new {
    border-left: 3px solid #3b82f6;
}

.stat-item-new:hover {
    background: #eff6ff;
}

.stat-item-active {
    border-left: 3px solid #f59e0b;
}

.stat-item-active:hover {
    background: #fffbeb;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
}

.stat-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: #003859;
    font-family: "Jost", sans-serif;
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

    .orders-stats {
        width: 100%;
        justify-content: space-around;
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
