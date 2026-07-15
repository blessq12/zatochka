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
                <div v-else class="history-list">
                    <article
                        v-for="ord in historyOrders"
                        :key="ord.id"
                        class="history-order-card"
                    >
                        <header class="history-order-header">
                            <div class="history-order-title">
                                <span class="history-order-number"
                                    >№{{ ord.order_number }}</span
                                >
                                <span class="history-order-date">{{
                                    formatDate(ord.created_at)
                                }}</span>
                            </div>
                            <span
                                class="history-order-status"
                                :class="statusClass(ord.status)"
                            >
                                {{ ord.status_label || statusLabel(ord.status) }}
                            </span>
                        </header>

                        <dl class="history-order-meta">
                            <div v-if="ord.client_name" class="meta-row">
                                <dt>Клиент</dt>
                                <dd>{{ ord.client_name }}</dd>
                            </div>
                            <div v-if="ord.master_name" class="meta-row">
                                <dt>Мастер</dt>
                                <dd>{{ ord.master_name }}</dd>
                            </div>
                        </dl>

                        <p
                            v-if="ord.problem_description"
                            class="history-problem"
                        >
                            {{ ord.problem_description }}
                        </p>

                        <div
                            v-if="ord.works && ord.works.length > 0"
                            class="history-works"
                        >
                            <h3 class="history-works-title">Работы</h3>
                            <ul class="history-works-list">
                                <li
                                    v-for="work in ord.works"
                                    :key="work.id || work.description"
                                    class="history-work-item"
                                >
                                    <span class="history-work-description">{{
                                        work.description
                                    }}</span>
                                    <span
                                        v-if="work.price"
                                        class="history-work-price"
                                        >{{ formatPrice(work.price) }}</span
                                    >
                                </li>
                            </ul>
                        </div>
                        <p v-else class="history-no-works">
                            Работы не зафиксированы
                        </p>

                        <div
                            v-if="ord.internal_notes"
                            class="history-notes"
                        >
                            <h3 class="history-works-title">Комментарии</h3>
                            <p class="history-notes-text">
                                {{ ord.internal_notes }}
                            </p>
                        </div>
                    </article>
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

        const statusClass = (status) => {
            const classes = {
                new: "status-new",
                in_work: "status-in-work",
                waiting_parts: "status-waiting-parts",
                ready: "status-ready",
                issued: "status-issued",
                cancelled: "status-cancelled",
            };

            return classes[status] || "";
        };

        const formatPrice = (price) => {
            const value = Number(price);

            if (Number.isNaN(value)) {
                return price;
            }

            return new Intl.NumberFormat("ru-RU", {
                style: "currency",
                currency: "RUB",
                maximumFractionDigits: 0,
            }).format(value);
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
            statusClass,
            formatPrice,
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

.history-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.history-order-card {
    border: 1px solid #e5e7eb;
    background: #fff;
    padding: 1rem;
}

.history-order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.history-order-title {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.5rem;
}

.history-order-number {
    font-weight: 700;
    color: #003859;
}

.history-order-date {
    font-size: 0.8125rem;
    color: #6b7280;
}

.history-order-status {
    padding: 0.2rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-new,
.status-issued {
    background: #dbeafe;
    color: #1e40af;
}

.status-in-work,
.status-waiting-parts {
    background: #fef3c7;
    color: #92400e;
}

.status-ready {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.history-order-meta {
    display: grid;
    gap: 0.35rem;
    margin: 0 0 0.75rem;
}

.meta-row {
    display: flex;
    gap: 0.5rem;
    font-size: 0.8125rem;
}

.meta-row dt {
    color: #6b7280;
    font-weight: 600;
    min-width: 4.5rem;
}

.meta-row dd {
    margin: 0;
    color: #374151;
}

.history-problem {
    margin: 0 0 0.75rem;
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.5;
}

.history-works-title {
    margin: 0 0 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.history-works-list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.history-work-item {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.5rem 0.65rem;
    background: rgba(0, 56, 89, 0.05);
    border: 1px solid rgba(0, 56, 89, 0.12);
    font-size: 0.875rem;
}

.history-work-description {
    color: #111827;
    line-height: 1.45;
}

.history-work-price {
    color: #003859;
    font-weight: 600;
    white-space: nowrap;
}

.history-no-works {
    margin: 0;
    font-size: 0.8125rem;
    color: #9ca3af;
    font-style: italic;
}

.history-notes {
    margin-top: 0.75rem;
}

.history-notes-text {
    margin: 0;
    padding: 0.5rem 0.65rem;
    background: rgba(194, 10, 108, 0.05);
    border: 1px solid rgba(194, 10, 108, 0.12);
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.45;
    white-space: pre-wrap;
}

.loading {
    padding: 1rem 0;
    color: #6b7280;
}
</style>
