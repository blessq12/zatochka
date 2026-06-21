<template>
    <div class="pos-page-content">
        <div class="page-body">
            <div v-if="isLoading && orders.length === 0" class="loading">
                Загрузка...
            </div>
            <div v-else-if="orders.length === 0" class="empty-state">
                <p>{{ tabConfig.emptyMessage }}</p>
            </div>
            <div v-else class="orders-list">
                <OrderCard
                    v-for="order in orders"
                    :key="order.id"
                    :order="order"
                    :show-client-name="isReadyTab"
                    :primary-action="Boolean(tabConfig.primaryAction)"
                    :primary-action-text="tabConfig.primaryAction?.text"
                    :primary-action-loading-text="
                        tabConfig.primaryAction?.loadingText ?? 'Сохранение...'
                    "
                    :primary-action-class="tabConfig.primaryAction?.className"
                    :is-loading="isUpdatingStatus"
                    @open-card="handleCardClick"
                    @primary-action="handlePrimaryAction"
                />
            </div>

            <div v-if="hasMore" class="pagination">
                <button
                    type="button"
                    class="btn-load-more"
                    :disabled="isLoadingMore"
                    @click="loadMore"
                >
                    {{
                        isLoadingMore
                            ? "Загрузка..."
                            : `Загрузить ещё (${orders.length} из ${listMeta.total})`
                    }}
                </button>
            </div>

            <OrderDetailsModal
                v-if="isReadyTab"
                :is-open="isModalOpen"
                :order-id="selectedOrderId"
                @close="closeOrderDetails"
            />
        </div>
    </div>
</template>

<script>
import { computed, reactive, ref, watch, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { orderService } from "../../services/pos/OrderService.js";
import { useAutoRefresh } from "../../composables/useAutoRefresh.js";
import { usePosStore } from "../../stores/posStore.js";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";
import {
    POS_ORDER_TABS,
    resolvePosOrderTabKey,
} from "../../composables/usePosOrderTabs.js";
import { toastService } from "../../services/toastService.js";
import OrderDetailsModal from "../../components/Pos/OrderDetailsModal.vue";
import OrderCard from "../../components/Pos/OrderCard.vue";
import OrderStats from "../../components/Pos/OrderStats.vue";

export default {
    name: "OrdersListPage",
    components: {
        OrderDetailsModal,
        OrderCard,
        OrderStats,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const orders = ref([]);
        const isLoading = ref(false);
        const isLoadingMore = ref(false);
        const isModalOpen = ref(false);
        const selectedOrderId = ref(null);
        const isUpdatingStatus = reactive({});
        const page = ref(1);
        const listMeta = ref({ total: 0, page: 1, per_page: 20 });
        const posStore = usePosStore();
        const { setCustomContent, reset } = useHeaderNavigation();

        const tabKey = computed(() => resolvePosOrderTabKey(route));
        const tabConfig = computed(() =>
            tabKey.value ? POS_ORDER_TABS[tabKey.value] : POS_ORDER_TABS.new
        );
        const isReadyTab = computed(() => tabKey.value === "ready");
        const hasMore = computed(
            () => orders.value.length < (listMeta.value.total ?? 0)
        );

        const fetchOrders = async (silent = false, append = false) => {
            if (!append) {
                if (!silent) {
                    isLoading.value = true;
                }
            } else {
                isLoadingMore.value = true;
            }

            try {
                const status = tabConfig.value.apiStatus;
                if (!status) {
                    orders.value = [];
                    listMeta.value = { total: 0, page: 1, per_page: 20 };
                    return;
                }

                const result = await orderService.getOrders(
                    status,
                    page.value
                );

                orders.value = append
                    ? [...orders.value, ...result.items]
                    : result.items;
                listMeta.value = result.meta;
            } catch (error) {
                console.error("Error fetching orders:", error);
            } finally {
                isLoading.value = false;
                isLoadingMore.value = false;
            }
        };

        const resetAndFetch = async (silent = false) => {
            page.value = 1;
            await fetchOrders(silent, false);
        };

        const loadMore = async () => {
            if (!hasMore.value || isLoadingMore.value) {
                return;
            }

            page.value += 1;
            await fetchOrders(true, true);
        };

        const openOrderDetails = (orderId) => {
            selectedOrderId.value = orderId;
            isModalOpen.value = true;
        };

        const closeOrderDetails = () => {
            isModalOpen.value = false;
            selectedOrderId.value = null;
        };

        const openDetail = (orderId) => {
            router.push({
                name: "pos.orders.detail",
                params: { id: orderId },
            });
        };

        const handleCardClick = (orderId) => {
            if (isReadyTab.value) {
                openOrderDetails(orderId);
                return;
            }

            openDetail(orderId);
        };

        const takeToWork = async (orderId) => {
            if (isUpdatingStatus[orderId]) {
                return;
            }

            isUpdatingStatus[orderId] = true;
            try {
                await orderService.takeToWork(orderId);
                toastService.success("Заказ взят в работу");
                await resetAndFetch();
                await posStore.getOrdersCount();
            } catch (error) {
                console.error("Error updating order status:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при изменении статуса заказа"
                );
            } finally {
                isUpdatingStatus[orderId] = false;
            }
        };

        const handlePrimaryAction = (orderId) => {
            const action = tabConfig.value.primaryAction;
            if (!action) {
                return;
            }

            if (action.type === "takeToWork") {
                takeToWork(orderId);
                return;
            }

            if (action.type === "openDetail") {
                openDetail(orderId);
                return;
            }

            if (action.type === "openModal") {
                openOrderDetails(orderId);
            }
        };

        watch(tabKey, () => {
            closeOrderDetails();
            resetAndFetch();
        });

        useAutoRefresh(() => resetAndFetch(true), 20000, true);

        onMounted(() => {
            setCustomContent({
                component: OrderStats,
                props: {},
            });
            resetAndFetch();
        });

        onUnmounted(() => {
            reset();
        });

        return {
            orders,
            isLoading,
            isLoadingMore,
            isModalOpen,
            selectedOrderId,
            isUpdatingStatus,
            tabConfig,
            isReadyTab,
            listMeta,
            hasMore,
            closeOrderDetails,
            handleCardClick,
            handlePrimaryAction,
            loadMore,
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
    font-family: "Jost", sans-serif;
}

.loading,
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
    font-weight: 500;
}

.orders-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1.25rem;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
}

.btn-load-more {
    padding: 0.75rem 1.25rem;
    background: white;
    border: 1px solid rgba(0, 56, 89, 0.25);
    color: #003859;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    font-family: "Jost", sans-serif;
    transition: all 0.2s;
}

.btn-load-more:hover:not(:disabled) {
    background: rgba(0, 56, 89, 0.06);
}

.btn-load-more:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .pos-page-content {
        padding: 0.75rem;
    }

    .loading,
    .empty-state {
        padding: 2rem 1rem;
        font-size: 0.875rem;
    }

    .orders-list {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
}
</style>
