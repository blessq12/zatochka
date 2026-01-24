<template>
    <div class="pos-page-content dashboard-page">
        <div class="page-header">
            <h1 class="page-title">Дашборд</h1>
        </div>

        <div v-if="isLoading" class="loading">
            Загрузка статистики...
        </div>
        <div v-else-if="error" class="error-state">
            <p>{{ error }}</p>
        </div>
        <div v-else-if="!stats" class="empty-state">
            <p>Нет данных</p>
        </div>
        <div v-else class="dashboard-content">
            <section class="stats-section">
                <h2 class="section-title">Статусы заказов</h2>
                <div class="stats-grid">
                    <div
                        v-for="key in STAT_KEYS"
                        :key="key"
                        class="stat-card"
                    >
                        <div class="stat-value">{{ stats.status_stats[key] ?? 0 }}</div>
                        <div class="stat-label">{{ getStatLabel(key) }}</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { onMounted, ref } from "vue";
import { useAutoRefresh } from "../../composables/useAutoRefresh.js";
import { orderService } from "../../services/pos/OrderService.js";

const STAT_KEYS = ["new", "in_work", "waiting_parts", "ready"];
const STAT_LABELS = {
    new: "Новых заказов",
    in_work: "В работе",
    waiting_parts: "Ожидание запчастей",
    ready: "Готовых заказов",
};

export default {
    name: "DashboardPage",
    setup() {
        const stats = ref(null);
        const isLoading = ref(false);
        const error = ref(null);

        const getStatLabel = (key) => STAT_LABELS[key] || key;

        const fetchStats = async (silent = false) => {
            if (!silent) {
                isLoading.value = true;
                error.value = null;
            }
            try {
                const res = await axios.get("/api/pos/dashboard");
                stats.value = res.data;
                error.value = null;
            } catch (e) {
                console.error("Error fetching dashboard stats:", e);
                if (!silent) {
                    error.value = e.response?.data?.message || "Ошибка загрузки. Проверьте соединение.";
                }
            } finally {
                if (!silent) isLoading.value = false;
            }
        };

        useAutoRefresh(fetchStats, 30000, true);
        onMounted(() => fetchStats());

        return {
            stats,
            isLoading,
            error,
            STAT_KEYS,
            getStatLabel,
            formatPrice: orderService.formatPrice,
        };
    },
};
</script>

<style scoped>
.dashboard-page {
    width: 100%;
    max-width: none;
    padding: 0;
    padding-top: 0.25rem;
    font-family: "Jost", sans-serif;
}

.page-header {
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #003859;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.loading,
.error-state,
.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    color: #6b7280;
    font-size: 1rem;
    font-weight: 500;
}

.error-state p,
.empty-state p {
    margin: 0;
}

.dashboard-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.stats-section {
    width: 100%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
    padding: 1.5rem 1.5rem 2rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    box-sizing: border-box;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #003859;
    margin: 0 0 1.25rem 0;
    font-family: "Jost", sans-serif;
    text-align: center;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
    padding: 1.25rem;
    transition: all 0.2s;
}

.stat-card:hover {
    border-color: rgba(0, 56, 89, 0.4);
    box-shadow: 0 4px 16px rgba(0, 56, 89, 0.1);
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #003859;
    line-height: 1.2;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
}

@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .page-title {
        font-size: 1.5rem;
    }

    .stats-section {
        padding: 1rem 1rem 1.5rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-value {
        font-size: 1.5rem;
    }
}
</style>
