<template>
    <div class="pos-page-content">
        <div class="page-header">
            <h1>Профиль</h1>
        </div>
        <div class="page-body">
            <div v-if="isLoading" class="loading">Загрузка...</div>
            <div v-else class="profile-card">
                <div class="profile-info">
                    <div class="info-row">
                        <span class="info-label">Имя:</span>
                        <span class="info-value">{{ user?.name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ user?.email }}</span>
                    </div>
                </div>
                <div class="profile-actions">
                    <button
                        @click="handleLogout"
                        class="logout-btn"
                    >
                        Выйти из учетной записи
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { usePosStore } from "../../stores/posStore.js";

export default {
    name: "SettingsProfilePage",
    setup() {
        const router = useRouter();
        const posStore = usePosStore();

        const user = computed(() => posStore.user);
        const isLoading = computed(() => posStore.isLoading);

        const handleLogout = async () => {
            await posStore.logout();
            router.push("/pos");
        };

        onMounted(() => {
            // Проверяем авторизацию при загрузке
            if (!posStore.isAuthenticated) {
                posStore.getMe();
            }
        });

        return {
            user,
            isLoading,
            handleLogout,
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

.loading {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.profile-card {
    max-width: 600px;
}

.profile-info {
    background: #f9fafb;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #374151;
}

.info-value {
    color: #6b7280;
}

.logout-btn {
    padding: 0.75rem 1.5rem;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.logout-btn:hover {
    background: #dc2626;
}
</style>
