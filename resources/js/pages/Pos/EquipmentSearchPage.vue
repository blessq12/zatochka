<template>
    <div class="pos-page-content equipment-search-page">
        <div class="page-header">
            <h1>Поиск оборудования</h1>
            <p class="page-subtitle">
                По названию, бренду, модели или серийному номеру — хронология
                ремонтов по карточке оборудования
            </p>
        </div>

        <div class="search-section">
            <input
                v-model="searchQuery"
                type="text"
                class="search-input"
                placeholder="Минимум 2 символа: название, SN, модель..."
                @input="onSearchInput"
            />
        </div>

        <div class="page-body two-col">
            <div class="col-results">
                <h2 class="col-title">Найдено</h2>
                <div v-if="isSearching" class="loading">Поиск...</div>
                <div
                    v-else-if="searchQuery.trim().length < 2"
                    class="empty-state"
                >
                    Введите запрос
                </div>
                <div v-else-if="matches.length === 0" class="empty-state">
                    Ничего не найдено
                </div>
                <ul v-else class="equipment-match-list">
                    <li
                        v-for="eq in matches"
                        :key="eq.id"
                        class="equipment-match-item"
                        :class="{ active: selectedId === eq.id }"
                        @click="selectEquipment(eq.id)"
                    >
                        <div class="eq-title">{{ eq.full_name || eq.name }}</div>
                        <div v-if="eq.serial_numbers_display" class="eq-sn">
                            {{ eq.serial_numbers_display }}
                        </div>
                    </li>
                </ul>
            </div>

            <div class="col-history">
                <h2 class="col-title">Хронология заказов</h2>
                <div v-if="!selectedId" class="empty-state">
                    Выберите оборудование слева
                </div>
                <div v-else-if="historyLoading" class="loading">
                    Загрузка...
                </div>
                <div v-else-if="historyOrders.length === 0" class="empty-state">
                    Заказов по этой единице пока нет
                </div>
                <div v-else class="history-table-wrap">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Дата</th>
                                <th>Статус</th>
                                <th>Мастер</th>
                                <th>Проблема</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="ord in historyOrders"
                                :key="ord.id"
                                class="history-row"
                            >
                                <td>№{{ ord.order_number }}</td>
                                <td>{{ formatDate(ord.created_at) }}</td>
                                <td>{{ statusLabel(ord.status) }}</td>
                                <td>{{ masterName(ord.master) }}</td>
                                <td class="problem-cell">
                                    {{ truncate(ord.problem_description, 80) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted, onUnmounted, ref } from "vue";
import { useRoute } from "vue-router";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";
import { equipmentService } from "../../services/pos/EquipmentService.js";
import { orderService } from "../../services/pos/OrderService.js";

export default {
    name: "EquipmentSearchPage",
    setup() {
        const route = useRoute();
        const { setNavigationItems, reset } = useHeaderNavigation();

        const searchQuery = ref("");
        const matches = ref([]);
        const isSearching = ref(false);
        const selectedId = ref(null);
        const historyLoading = ref(false);
        const historyOrders = ref([]);
        let debounceTimer = null;

        const formatDate = (d) => {
            if (!d) return "—";
            return new Intl.DateTimeFormat("ru-RU", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            }).format(new Date(d));
        };

        const statusLabel = (s) => orderService.getStatusLabel(s);

        const masterName = (m) => {
            if (!m) return "—";
            if (m.surname) {
                return `${m.surname} ${m.name}`.trim();
            }
            return m.name || "—";
        };

        const truncate = (text, n) => {
            if (!text) return "—";
            return text.length <= n ? text : text.slice(0, n) + "…";
        };

        const runSearch = async () => {
            const q = searchQuery.value.trim();
            if (q.length < 2) {
                matches.value = [];
                return;
            }
            isSearching.value = true;
            try {
                matches.value = await equipmentService.search(q);
            } catch (e) {
                console.error(e);
                matches.value = [];
            } finally {
                isSearching.value = false;
            }
        };

        const onSearchInput = () => {
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            debounceTimer = setTimeout(runSearch, 350);
        };

        const selectEquipment = async (id) => {
            selectedId.value = id;
            historyLoading.value = true;
            historyOrders.value = [];
            try {
                const { orders } = await equipmentService.getOrderHistory(id);
                historyOrders.value = orders;
            } catch (e) {
                console.error(e);
                historyOrders.value = [];
            } finally {
                historyLoading.value = false;
            }
        };

        onMounted(() => {
            setNavigationItems([
                {
                    name: "equipment-search",
                    label: "Поиск оборудования",
                    to: { name: "pos.equipment.search" },
                    active: route.name === "pos.equipment.search",
                },
            ]);
        });

        onUnmounted(() => {
            reset();
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
        });

        return {
            searchQuery,
            matches,
            isSearching,
            selectedId,
            historyLoading,
            historyOrders,
            onSearchInput,
            selectEquipment,
            formatDate,
            statusLabel,
            masterName,
            truncate,
        };
    },
};
</script>

<style scoped>
.pos-page-content {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
    font-family: "Jost", sans-serif;
}

.equipment-search-page .page-header {
    margin-bottom: 1rem;
}

.equipment-search-page .page-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #003859;
    margin: 0;
}

.page-subtitle {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0.5rem 0 0;
    max-width: 42rem;
}

.search-section {
    margin-bottom: 1.25rem;
}

.search-input {
    width: 100%;
    max-width: 32rem;
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    font-size: 1rem;
    font-family: "Jost", sans-serif;
}

.page-body.two-col {
    display: grid;
    grid-template-columns: minmax(260px, 1fr) minmax(320px, 2fr);
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 900px) {
    .page-body.two-col {
        grid-template-columns: 1fr;
    }
}

.col-title {
    font-size: 1rem;
    margin: 0 0 0.75rem;
    color: #003859;
    font-family: "Jost", sans-serif;
}

.empty-state {
    color: #6b7280;
    font-size: 0.875rem;
    padding: 1rem 0;
}

.equipment-match-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.equipment-match-item {
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    background: #fff;
    cursor: pointer;
    text-align: left;
    transition:
        border-color 0.15s,
        background 0.15s;
}

.equipment-match-item:hover {
    border-color: #003859;
}

.equipment-match-item.active {
    border-color: #c20a6c;
    background: rgba(194, 10, 108, 0.06);
}

.eq-title {
    font-weight: 600;
    color: #111827;
    font-size: 0.9375rem;
}

.eq-sn {
    font-size: 0.8125rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.history-table-wrap {
    overflow-x: auto;
    border: 1px solid #e5e7eb;
    background: #fff;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.history-table th,
.history-table td {
    padding: 0.6rem 0.75rem;
    text-align: left;
    border-bottom: 1px solid #f3f4f6;
}

.history-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.problem-cell {
    max-width: 14rem;
    color: #4b5563;
}

.loading {
    padding: 1rem 0;
    color: #6b7280;
}
</style>
