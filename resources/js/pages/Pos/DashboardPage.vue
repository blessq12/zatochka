<template>
    <div class="pos-page-content dashboard-page">
        <div class="page-header">
            <h1>Дашборд</h1>
        </div>

        <div v-if="isLoading" class="loading">Загрузка статистики...</div>
        <div v-else-if="stats" class="dashboard-content">
            <!-- Статистика по статусам заказов -->
            <div class="stats-section">
                <h2 class="section-title">Статусы заказов</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-value">{{ stats.status_stats.new }}</div>
                            <div class="stat-label">Новых заказов</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-value">{{ stats.status_stats.in_work }}</div>
                            <div class="stat-label">В работе</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-value">{{ stats.status_stats.waiting_parts }}</div>
                            <div class="stat-label">Ожидание запчастей</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-value">{{ stats.status_stats.ready }}</div>
                            <div class="stat-label">Готовых заказов</div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</template>

<script>
import axios from "axios";
import { onMounted, ref } from "vue";
import { useAutoRefresh } from "../../composables/useAutoRefresh.js";
import { orderService } from "../../services/pos/OrderService.js";

export default {
    name: "DashboardPage",
    setup() {
        const stats = ref(null);
        const isLoading = ref(false);

        const fetchStats = async (silent = false) => {
            if (!silent) {
                isLoading.value = true;
            }
            try {
                const response = await axios.get("/api/pos/dashboard");
                stats.value = response.data;
            } catch (error) {
                console.error("Error fetching dashboard stats:", error);
            } finally {
                if (!silent) {
                    isLoading.value = false;
                }
            }
        };

        // Автообновление статистики каждые 30 секунд
        useAutoRefresh(fetchStats, 30000, true);

        onMounted(() => {
            fetchStats();
        });

        return {
            stats,
            isLoading,
            formatPrice: orderService.formatPrice,
        };
    },
};
</script>

<style scoped>
.dashboard-page {
    max-width: 1400px;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #003859;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.dashboard-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.stats-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #003859;
    margin: 0 0 1.5rem 0;
    font-family: "Jost", sans-serif;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: #003859;
}

.stat-icon {
    font-size: 2.5rem;
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #003859;
    line-height: 1.2;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.period-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.period-stat-card {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 2px solid #3b82f6;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.2s;
}

.period-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.period-stat-card.works-card {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border-color: #059669;
}

.period-stat-card.works-card:hover {
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
}

.period-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}

.period-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #003859;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

.period-revenue {
    font-size: 1.25rem;
    font-weight: 700;
    color: #059669;
}

.loading {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
    font-size: 1.125rem;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .dashboard-page {
        padding: 0.75rem;
        border-radius: 8px;
    }

    .page-header h1 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .dashboard-content {
        gap: 1rem;
    }

    .stats-section {
        padding: 1rem;
        border-radius: 8px;
    }

    .section-title {
        font-size: 1.125rem;
        margin-bottom: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        font-size: 2rem;
    }

    .stat-value {
        font-size: 1.5rem;
    }

    .period-stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .period-stat-card {
        padding: 1rem;
    }

    .period-value {
        font-size: 2rem;
    }

    .period-revenue {
        font-size: 1.125rem;
    }
}
</style>
