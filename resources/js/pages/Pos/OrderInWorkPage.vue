<template>
    <div class="pos-page-content order-in-work-page">
        <div v-if="isLoading" class="loading">Загрузка...</div>
        <div v-else-if="!order" class="error-state">
            <p>Заказ не найден</p>
            <router-link :to="{ name: 'pos.orders.active' }" class="btn-back">
                Вернуться к активным заказам
            </router-link>
        </div>
        <div v-else class="order-content">
            <!-- Заголовок с кнопками действий -->
            <div class="order-header-section">
                <div class="order-header-top">
                    <div class="order-title-group">
                        <h1 class="order-title">Заказ №{{ order.order_number }}</h1>
                        <div class="order-status-group">
                            <span
                                class="order-status-badge"
                                :class="getStatusClass(order.status)"
                            >
                                {{ getStatusLabel(order.status) }}
                            </span>
                            <span
                                v-if="order.urgency === 'urgent'"
                                class="urgency-badge urgent"
                            >
                                ⚡ Срочно
                            </span>
                        </div>
                    </div>
                    <router-link
                        :to="{ name: 'pos.orders.active' }"
                        class="btn-back"
                    >
                        ← Назад
                    </router-link>
                </div>
                
                <!-- Кнопки изменения статуса -->
                <div class="status-actions">
                    <button
                        @click="setInWorkStatus"
                        class="btn-status btn-in-work"
                        :disabled="
                            isChangingStatus || order.status === 'in_work'
                        "
                    >
                        <span
                            v-if="
                                isChangingStatus &&
                                changingToStatus === 'in_work'
                            "
                            >Сохранение...</span
                        >
                        <span v-else>В работе</span>
                    </button>
                    <button
                        @click="setWaitingPartsStatus"
                        class="btn-status btn-waiting-parts"
                        :disabled="
                            isChangingStatus ||
                            order.status === 'waiting_parts'
                        "
                    >
                        <span
                            v-if="
                                isChangingStatus &&
                                changingToStatus === 'waiting_parts'
                            "
                            >Сохранение...</span
                        >
                        <span v-else>Ожидание запчастей</span>
                    </button>
                    <button
                        @click="completeOrder"
                        class="btn-status btn-complete"
                        :disabled="
                            isCompletingOrder || order.status === 'ready'
                        "
                    >
                        <span v-if="isCompletingOrder">Сохранение...</span>
                        <span v-else>Завершить заказ</span>
                    </button>
                </div>
            </div>

            <!-- Информация о заказе -->
            <div class="order-info-section">
                <h2 class="section-title">Информация о заказе</h2>
                
                <div class="order-info-grid">
                    <!-- Клиент -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <span class="info-icon">👤</span>
                            <span class="info-card-title">Клиент</span>
                        </div>
                        <div class="info-card-content">
                            <div class="info-card-item">
                                <span class="info-card-label">ФИО</span>
                                <span class="info-card-value">{{ order.client?.full_name || "—" }}</span>
                            </div>
                            <div v-if="order.client?.phone" class="info-card-item">
                                <span class="info-card-label">Телефон</span>
                                <a :href="`tel:${order.client.phone}`" class="info-card-value link">
                                    {{ order.client.phone }}
                                </a>
                            </div>
                            <div v-if="order.client?.email" class="info-card-item">
                                <span class="info-card-label">Email</span>
                                <a :href="`mailto:${order.client.email}`" class="info-card-value link">
                                    {{ order.client.email }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Услуга и филиал -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <span class="info-icon">🔧</span>
                            <span class="info-card-title">Услуга</span>
                        </div>
                        <div class="info-card-content">
                            <div class="info-card-item">
                                <span class="info-card-label">Тип услуги</span>
                                <span class="info-card-value">{{ getTypeLabel(order.service_type) }}</span>
                            </div>
                            <div v-if="order.branch?.name" class="info-card-item">
                                <span class="info-card-label">Филиал</span>
                                <span class="info-card-value">{{ order.branch.name }}</span>
                            </div>
                            <div v-if="order.order_payment_type" class="info-card-item">
                                <span class="info-card-label">Тип оплаты</span>
                                <span class="info-card-value">
                                    {{ order.order_payment_type === 'paid' ? 'Платный' : 'Гарантия' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Оборудование (если есть) -->
                    <div v-if="order.equipment?.name || order.equipment_name" class="info-card">
                        <div class="info-card-header">
                            <span class="info-icon">⚙️</span>
                            <span class="info-card-title">Оборудование</span>
                        </div>
                        <div class="info-card-content">
                            <div class="info-card-item">
                                <span class="info-card-label">Название</span>
                                <span class="info-card-value">
                                    {{ order.equipment?.name || order.equipment_name }}
                                </span>
                            </div>
                            <div v-if="order.equipment?.serial_number || order.equipment_serial_number" class="info-card-item">
                                <span class="info-card-label">Серийный номер</span>
                                <span class="info-card-value">
                                    {{ order.equipment?.serial_number || order.equipment_serial_number }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Инструменты для заточки (если есть) -->
                    <div v-if="order.tools && order.tools.length > 0" class="info-card">
                        <div class="info-card-header">
                            <span class="info-icon">✂️</span>
                            <span class="info-card-title">Инструменты</span>
                        </div>
                        <div class="info-card-content">
                            <div 
                                v-for="(tool, idx) in order.tools" 
                                :key="tool.id || idx"
                                class="info-card-item"
                            >
                                <span class="info-card-label">{{ tool.tool_type }}</span>
                                <span class="info-card-value">
                                    Количество: {{ tool.quantity }}
                                    <span v-if="tool.description" class="info-card-subvalue">
                                        ({{ tool.description }})
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Описание проблемы -->
                <div v-if="order.problem_description" class="problem-card">
                    <div class="problem-card-header">
                        <span class="info-icon">📝</span>
                        <span class="problem-card-title">Описание проблемы</span>
                    </div>
                    <div class="problem-card-content">
                        {{ order.problem_description }}
                    </div>
                </div>

                <!-- Комментарии мастера -->
                <div class="comments-section">
                    <h3 class="section-title">Комментарии мастера</h3>
                    <div class="comments-list" v-if="order.internal_notes">
                        <div class="comment-item">
                            <div class="comment-content">
                                {{ order.internal_notes }}
                            </div>
                        </div>
                    </div>
                    <form @submit.prevent="saveComment" class="comment-form">
                        <div class="form-group">
                            <label class="form-label"
                                >Добавить комментарий</label
                            >
                            <textarea
                                v-model="commentForm.internal_notes"
                                class="form-textarea"
                                rows="3"
                                placeholder="Введите комментарий к заказу..."
                            ></textarea>
                        </div>
                        <button
                            type="submit"
                            class="btn-primary btn-save-comment"
                            :disabled="isSavingComment"
                        >
                            <span v-if="isSavingComment">Сохранение...</span>
                            <span v-else>Сохранить комментарий</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Выполненные работы -->
            <div class="works-section">
                <h2 class="section-title">Выполненные работы</h2>

                <!-- Список работ -->
                <div v-if="worksLoading" class="loading">Загрузка работ...</div>
                <div
                    v-if="!worksLoading && works.length > 0"
                    class="works-list"
                >
                    <div v-for="work in works" :key="work.id" class="work-item">
                        <div class="work-header">
                            <span class="work-price"
                                >{{ formatPrice(work.work_price || 0) }} ₽</span
                            >
                            <button
                                @click="deleteWork(work.id)"
                                class="btn-delete"
                                :disabled="isDeletingWork[work.id]"
                            >
                                Удалить
                            </button>
                        </div>
                        <div class="work-description">
                            {{ work.description }}
                        </div>
                    </div>
                </div>

                <!-- Форма добавления работы -->
                <div class="add-work-form">
                    <form @submit.prevent="addWork" class="work-form">
                        <div class="form-row-inline">
                            <div class="form-group flex-1">
                                <label class="form-label"
                                    >Описание работы *</label
                                >
                                <input
                                    v-model="workForm.description"
                                    type="text"
                                    class="form-input"
                                    placeholder="Опишите выполненную работу"
                                    required
                                />
                            </div>
                            <div class="form-group form-group-price">
                                <label class="form-label">Цена *</label>
                                <input
                                    v-model.number="workForm.work_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-input"
                                    placeholder="0.00"
                                    required
                                />
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="btn-primary btn-add-work"
                            :disabled="isAddingWork"
                        >
                            <span v-if="isAddingWork">Сохранение...</span>
                            <span v-else>+ Добавить работу</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Материалы к работам -->
            <div class="materials-section">
                <h2 class="section-title">Материалы и запчасти</h2>

                <!-- Список материалов -->
                <div v-if="materialsLoading" class="loading">
                    Загрузка материалов...
                </div>
                <div
                    v-if="!materialsLoading && materials.length > 0"
                    class="materials-list"
                >
                    <div
                        v-for="material in materials"
                        :key="`${material.work_id}-${material.id}`"
                        class="material-item"
                    >
                        <div class="material-info">
                            <span class="material-name">{{
                                material.name
                            }}</span>
                            <span
                                v-if="material.article"
                                class="material-article"
                                >Арт: {{ material.article }}</span
                            >
                        </div>
                        <div class="material-details">
                            <span>Количество: {{ material.quantity }}</span>
                            <span
                                >Цена: {{ formatPrice(material.price) }} ₽</span
                            >
                            <span
                                >Сумма:
                                {{
                                    formatPrice(
                                        material.quantity * material.price
                                    )
                                }}
                                ₽</span
                            >
                        </div>
                        <button
                            @click="
                                removeMaterial(
                                    material.work_id,
                                    material.id
                                )
                            "
                            class="btn-delete"
                            :disabled="
                                isRemovingMaterial[
                                    `${material.work_id}-${material.id}`
                                ]
                            "
                        >
                            Удалить
                        </button>
                    </div>
                </div>

                <!-- Форма добавления материала -->
                <div class="add-material-form">
                    <form @submit.prevent="addMaterial" class="material-form">
                        <div class="material-form-row">
                            <div class="material-form-search">
                                <label class="form-label">Материал или запчасть *</label>
                                <button
                                    type="button"
                                    class="btn-select-material"
                                    @click="openMaterialSearchModal"
                                >
                                    <span v-if="selectedMaterialName">
                                        {{ selectedMaterialName }}
                                    </span>
                                    <span v-else class="placeholder-text">
                                        Выберите материал...
                                    </span>
                                    <span class="btn-select-arrow">▼</span>
                                </button>
                                <input
                                    v-model="materialForm.warehouse_item_id"
                                    type="hidden"
                                />
                            </div>
                            <div class="material-form-quantity">
                                <label class="form-label">Количество *</label>
                                <input
                                    v-model.number="materialForm.quantity"
                                    type="number"
                                    step="0.001"
                                    min="0.001"
                                    class="form-input quantity-input"
                                    placeholder="1.000"
                                    required
                                />
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="btn-primary btn-add-material"
                            :disabled="isAddingMaterial || !materialForm.warehouse_item_id"
                        >
                            <span v-if="isAddingMaterial">Сохранение...</span>
                            <span v-else>+ Добавить запчасть/материал</span>
                        </button>
                    </form>
                </div>

                <!-- Модалка поиска материалов -->
                <div
                    v-if="showMaterialSearchModal"
                    class="modal-overlay"
                    @click.self="closeMaterialSearchModal"
                >
                    <div class="modal-container material-search-modal">
                        <div class="modal-header">
                            <h2 class="modal-title">Поиск материала</h2>
                            <button
                                @click="closeMaterialSearchModal"
                                class="modal-close-btn"
                            >
                                ✕
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="search-input-wrapper">
                                <input
                                    v-model="materialSearchQuery"
                                    type="text"
                                    class="form-input search-input-full"
                                    placeholder="Введите название материала или артикул..."
                                    @input="handleMaterialSearch"
                                    autofocus
                                />
                                <div v-if="isSearching" class="search-loading">
                                    Поиск...
                                </div>
                            </div>

                            <div
                                v-if="materialSearchResults.length > 0"
                                class="search-results-list"
                            >
                                <div
                                    v-for="item in materialSearchResults"
                                    :key="item.id"
                                    class="search-result-item-modal"
                                    @click="selectMaterialFromModal(item)"
                                >
                                    <div class="result-header">
                                        <div class="result-name">{{ item.name }}</div>
                                        <div v-if="item.article" class="result-article">
                                            Арт: {{ item.article }}
                                        </div>
                                    </div>
                                    <div class="result-footer">
                                        <span class="result-category">
                                            {{ item.category?.name || "—" }}
                                        </span>
                                        <span class="result-available">
                                            Доступно:
                                            {{
                                                item.quantity - item.reserved_quantity || 0
                                            }}
                                            {{ item.unit }}
                                        </span>
                                        <span class="result-price" v-if="item.price">
                                            {{ formatPrice(item.price) }} ₽
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else-if="
                                    materialSearchQuery.trim() &&
                                    !isSearching
                                "
                                class="no-results-message"
                            >
                                Ничего не найдено
                            </div>
                            <div
                                v-else-if="!materialSearchQuery.trim()"
                                class="no-results-message"
                            >
                                Введите название материала или артикул для поиска
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { orderService } from "../../services/pos/OrderService.js";
import { warehouseService } from "../../services/pos/WarehouseService.js";
import { usePosStore } from "../../stores/posStore.js";
import axios from "axios";
import { toastService } from "../../services/toastService.js";

export default {
    name: "OrderInWorkPage",
    setup() {
        const route = useRoute();
        const router = useRouter();
        const orderId = computed(() => route.params.id);

        const order = ref(null);
        const isLoading = ref(false);
        const works = ref([]);
        const worksLoading = ref(false);
        const materials = ref([]);
        const materialsLoading = ref(false);
        const warehouseItems = ref([]);

        const workForm = reactive({
            description: "",
            work_price: null,
        });

        const materialForm = reactive({
            warehouse_item_id: "",
            quantity: 1,
        });

        const materialSearchQuery = ref("");
        const materialSearchResults = ref([]);
        const showMaterialSearchModal = ref(false);
        const selectedMaterialName = ref("");
        const isSearching = ref(false);
        let searchDebounceTimer = null;

        const isAddingWork = ref(false);
        const isAddingMaterial = ref(false);
        const isDeletingWork = reactive({});
        const isRemovingMaterial = reactive({});
        const isCompletingOrder = ref(false);
        const isChangingStatus = ref(false);
        const changingToStatus = ref(null);
        const isSavingComment = ref(false);

        const commentForm = reactive({
            internal_notes: "",
        });

        const fetchOrder = async () => {
            isLoading.value = true;
            try {
                const orderData = await orderService.getOrderById(
                    orderId.value
                );
                order.value = orderData;
            } catch (error) {
                console.error("Error fetching order:", error);
                toastService.error("Ошибка при загрузке заказа");
            } finally {
                isLoading.value = false;
            }
        };

        const fetchWorks = async () => {
            worksLoading.value = true;
            try {
                const response = await axios.get(
                    `/api/pos/orders/${orderId.value}/works`
                );
                works.value = response.data.works || [];
            } catch (error) {
                console.error("Error fetching works:", error);
                toastService.error("Ошибка при загрузке работ");
            } finally {
                worksLoading.value = false;
            }
        };

        const fetchMaterials = async () => {
            materialsLoading.value = true;
            try {
                const response = await axios.get(
                    `/api/pos/orders/${orderId.value}/materials`
                );
                materials.value = response.data.materials || [];
            } catch (error) {
                console.error("Error fetching materials:", error);
                toastService.error("Ошибка при загрузке материалов");
            } finally {
                materialsLoading.value = false;
            }
        };

        const fetchWarehouseItems = async () => {
            try {
                const response = await axios.get("/api/pos/warehouse/items");
                warehouseItems.value = response.data.items || [];
            } catch (error) {
                console.error("Error fetching warehouse items:", error);
            }
        };

        const handleMaterialSearch = async () => {
            const query = materialSearchQuery.value.trim();

            // Очищаем предыдущий таймер
            if (searchDebounceTimer) {
                clearTimeout(searchDebounceTimer);
            }

            if (!query) {
                materialSearchResults.value = [];
                return;
            }

            // Дебаунс поиска - ждем 300мс после последнего ввода
            searchDebounceTimer = setTimeout(async () => {
                isSearching.value = true;
                try {
                    // Ищем через API по всем элементам склада
                    const result = await warehouseService.getAllItems(
                        1,
                        50, // Лимит для модалки
                        query
                    );

                    materialSearchResults.value = result.items;
                } catch (error) {
                    console.error("Error searching materials:", error);
                    materialSearchResults.value = [];
                } finally {
                    isSearching.value = false;
                }
            }, 300);
        };

        const openMaterialSearchModal = () => {
            showMaterialSearchModal.value = true;
            materialSearchQuery.value = "";
            materialSearchResults.value = [];
        };

        const closeMaterialSearchModal = () => {
            showMaterialSearchModal.value = false;
            materialSearchQuery.value = "";
            materialSearchResults.value = [];
        };

        const selectMaterialFromModal = (item) => {
            materialForm.warehouse_item_id = item.id;
            selectedMaterialName.value = item.name;
            closeMaterialSearchModal();
        };

        const addWork = async () => {
            isAddingWork.value = true;
            try {
                await axios.post(
                    `/api/pos/orders/${orderId.value}/works`,
                    workForm
                );
                toastService.success("Работа добавлена");

                // Очищаем форму
                workForm.description = "";
                workForm.work_price = null;

                // Обновляем список работ
                await fetchWorks();
            } catch (error) {
                console.error("Error adding work:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при добавлении работы"
                );
            } finally {
                isAddingWork.value = false;
            }
        };

        const deleteWork = async (workId) => {
            if (!confirm("Удалить эту работу?")) return;

            isDeletingWork[workId] = true;
            try {
                await axios.delete(
                    `/api/pos/orders/${orderId.value}/works/${workId}`
                );
                toastService.success("Работа удалена");
                await fetchWorks();
                await fetchMaterials(); // Обновляем материалы, так как они привязаны к работам
            } catch (error) {
                console.error("Error deleting work:", error);
                toastService.error("Ошибка при удалении работы");
            } finally {
                isDeletingWork[workId] = false;
            }
        };

        const addMaterial = async () => {
            if (!materialForm.warehouse_item_id) {
                toastService.error("Выберите материал/запчасть");
                return;
            }

            // Проверяем наличие работ
            if (works.value.length === 0) {
                toastService.error("Сначала добавьте работу к заказу");
                return;
            }

            // Используем первую работу
            const workId = works.value[0].id;

            isAddingMaterial.value = true;
            try {
                await axios.post(
                    `/api/pos/orders/${orderId.value}/works/${workId}/materials`,
                    {
                        warehouse_item_id: materialForm.warehouse_item_id,
                        quantity: materialForm.quantity,
                    }
                );
                toastService.success("Материал добавлен");

                // Очищаем форму
                materialForm.warehouse_item_id = "";
                materialForm.quantity = 1;
                selectedMaterialName.value = "";

                // Обновляем список материалов
                await fetchMaterials();
                await fetchWarehouseItems(); // Обновляем остатки на складе
            } catch (error) {
                console.error("Error adding material:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при добавлении материала"
                );
            } finally {
                isAddingMaterial.value = false;
            }
        };

        const removeMaterial = async (workId, materialId) => {
            if (!confirm("Удалить этот материал?")) return;

            const key = `${workId}-${materialId}`;
            isRemovingMaterial[key] = true;
            try {
                await axios.delete(
                    `/api/pos/orders/${orderId.value}/works/${workId}/materials/${materialId}`
                );
                toastService.success("Материал удален");
                await fetchMaterials();
                await fetchWarehouseItems(); // Обновляем остатки на складе
            } catch (error) {
                console.error("Error removing material:", error);
                toastService.error("Ошибка при удалении материала");
            } finally {
                isRemovingMaterial[key] = false;
            }
        };

        const setInWorkStatus = async () => {
            if (!confirm("Перевести заказ в статус 'В работе'?")) {
                return;
            }

            isChangingStatus.value = true;
            changingToStatus.value = "in_work";
            try {
                await orderService.updateOrderStatus(orderId.value, "in_work");
                toastService.success("Заказ переведен в работу");

                // Обновляем данные заказа
                await fetchOrder();

                // Обновляем счетчики заказов
                const posStore = usePosStore();
                await posStore.getOrdersCount();
            } catch (error) {
                console.error("Error updating order status:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при изменении статуса заказа"
                );
            } finally {
                isChangingStatus.value = false;
                changingToStatus.value = null;
            }
        };

        const setWaitingPartsStatus = async () => {
            if (!confirm("Перевести заказ в статус 'Ожидание запчастей'?")) {
                return;
            }

            isChangingStatus.value = true;
            changingToStatus.value = "waiting_parts";
            try {
                await orderService.updateOrderStatus(
                    orderId.value,
                    "waiting_parts"
                );
                toastService.success("Заказ переведен в ожидание запчастей");

                // Обновляем данные заказа
                await fetchOrder();

                // Обновляем счетчики заказов
                const posStore = usePosStore();
                await posStore.getOrdersCount();
            } catch (error) {
                console.error("Error updating order status:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при изменении статуса заказа"
                );
            } finally {
                isChangingStatus.value = false;
                changingToStatus.value = null;
            }
        };

        const saveComment = async () => {
            isSavingComment.value = true;
            try {
                await axios.patch(`/api/pos/orders/${orderId.value}/update`, {
                    internal_notes: commentForm.internal_notes,
                });
                toastService.success("Комментарий сохранен");

                // Обновляем данные заказа
                await fetchOrder();

                // Очищаем форму после сохранения
                commentForm.internal_notes = "";
            } catch (error) {
                console.error("Error saving comment:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при сохранении комментария"
                );
            } finally {
                isSavingComment.value = false;
            }
        };

        const completeOrder = async () => {
            if (
                !confirm(
                    "Завершить заказ? Заказ будет переведен в статус 'Готов'."
                )
            ) {
                return;
            }

            isCompletingOrder.value = true;
            try {
                await orderService.updateOrderStatus(orderId.value, "ready");
                toastService.success("Заказ завершен");

                // Обновляем данные заказа
                await fetchOrder();

                // Обновляем счетчики заказов
                const posStore = usePosStore();
                await posStore.getOrdersCount();

                // Перенаправляем на страницу активных заказов через небольшую задержку
                setTimeout(() => {
                    router.push({ name: "pos.orders.active" });
                }, 1500);
            } catch (error) {
                console.error("Error completing order:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при завершении заказа"
                );
            } finally {
                isCompletingOrder.value = false;
            }
        };

        // Следим за изменениями заказа и обновляем форму комментариев
        const updateCommentForm = () => {
            if (order.value?.internal_notes && !commentForm.internal_notes) {
                commentForm.internal_notes = order.value.internal_notes;
            }
        };

        onMounted(async () => {
            await Promise.all([
                fetchOrder(),
                fetchWorks(),
                fetchMaterials(),
                fetchWarehouseItems(),
            ]);

            // Заполняем форму комментариев существующим значением
            updateCommentForm();
        });

        return {
            order,
            isLoading,
            works,
            worksLoading,
            materials,
            materialsLoading,
            warehouseItems,
            workForm,
            materialForm,
            materialSearchQuery,
            materialSearchResults,
            showMaterialSearchModal,
            selectedMaterialName,
            isSearching,
            handleMaterialSearch,
            openMaterialSearchModal,
            closeMaterialSearchModal,
            selectMaterialFromModal,
            isAddingWork,
            isAddingMaterial,
            isDeletingWork,
            isRemovingMaterial,
            isCompletingOrder,
            isChangingStatus,
            changingToStatus,
            isSavingComment,
            commentForm,
            setInWorkStatus,
            setWaitingPartsStatus,
            saveComment,
            completeOrder,
            addWork,
            deleteWork,
            addMaterial,
            removeMaterial,
            getStatusLabel: orderService.getStatusLabel,
            getTypeLabel: orderService.getTypeLabel,
            formatPrice: orderService.formatPrice,
            getStatusClass: (status) => {
                const classes = {
                    new: "status-new",
                    consultation: "status-consultation",
                    diagnostic: "status-diagnostic",
                    in_work: "status-in-work",
                    waiting_parts: "status-waiting-parts",
                    ready: "status-ready",
                    issued: "status-issued",
                    cancelled: "status-cancelled",
                };
                return classes[status] || "";
            },
        };
    },
};
</script>

<style scoped>
.order-in-work-page {
    max-width: 1200px;
}

.order-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Заголовок заказа */
.order-header-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.order-header-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.order-title-group {
    flex: 1;
    min-width: 0;
}

.order-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #003859;
    margin: 0 0 0.75rem 0;
    font-family: "Jost", sans-serif;
}

.order-status-group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
}

.order-status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 600;
    white-space: nowrap;
}

.order-status-badge.status-new,
.order-status-badge.status-consultation,
.order-status-badge.status-diagnostic {
    background: #dbeafe;
    color: #1e40af;
}

.order-status-badge.status-in-work,
.order-status-badge.status-waiting-parts {
    background: #fef3c7;
    color: #92400e;
}

.order-status-badge.status-ready {
    background: #d1fae5;
    color: #065f46;
}

.order-status-badge.status-issued {
    background: #dbeafe;
    color: #1e40af;
}

.order-status-badge.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.urgency-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.urgency-badge.urgent {
    background: #fee2e2;
    color: #991b1b;
}

.status-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-status {
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.btn-status:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-status:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-status.btn-in-work {
    background: #3b82f6;
    color: white;
}

.btn-status.btn-in-work:hover:not(:disabled) {
    background: #2563eb;
}

.btn-status.btn-in-work:disabled {
    background: #9ca3af;
}

.btn-status.btn-waiting-parts {
    background: #f59e0b;
    color: white;
}

.btn-status.btn-waiting-parts:hover:not(:disabled) {
    background: #d97706;
}

.btn-status.btn-waiting-parts:disabled {
    background: #9ca3af;
}

.btn-status.btn-complete {
    background: #059669;
    color: white;
}

.btn-status.btn-complete:hover:not(:disabled) {
    background: #047857;
}

.btn-status.btn-complete:disabled {
    background: #9ca3af;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #003859;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.btn-back {
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    color: #374151;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #e5e7eb;
}

.order-info-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.info-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.2s;
}

.info-card:hover {
    border-color: #003859;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.info-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.info-icon {
    font-size: 1.125rem;
}

.info-card-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #003859;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-card-content {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-card-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-card-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-card-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    word-break: break-word;
}

.info-card-value.link {
    color: #046490;
    text-decoration: none;
}

.info-card-value.link:hover {
    text-decoration: underline;
}

.info-card-subvalue {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.125rem;
}

.problem-card {
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.problem-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.problem-card-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #92400e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.problem-card-content {
    font-size: 0.875rem;
    color: #78350f;
    line-height: 1.6;
    white-space: pre-wrap;
}

.works-section,
.materials-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.works-list,
.materials-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.work-item,
.material-item {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    background: #f9fafb;
    transition: all 0.2s;
}

.work-item:hover,
.material-item:hover {
    border-color: #003859;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.work-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    gap: 1rem;
}

.work-price {
    font-weight: 700;
    color: #059669;
    font-size: 1.125rem;
}

.work-description {
    color: #374151;
    line-height: 1.6;
    font-size: 0.875rem;
}

.material-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.material-name {
    font-weight: 600;
    color: #374151;
    font-size: 0.9375rem;
}

.material-article {
    font-size: 0.8125rem;
    color: #6b7280;
    font-family: monospace;
}

.material-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.8125rem;
    color: #6b7280;
    margin-bottom: 0.75rem;
}

.material-details span {
    padding: 0.25rem 0.5rem;
    background: #f3f4f6;
    border-radius: 4px;
}

.add-work-form,
.add-material-form {
    border-top: 2px solid #e5e7eb;
    padding-top: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-row-inline {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    margin-bottom: 1rem;
}

.form-group.flex-1 {
    flex: 1;
}

.form-group-price {
    min-width: 150px;
    max-width: 200px;
}

.form-group-quantity {
    min-width: 120px;
    max-width: 150px;
}

.material-form-row {
    display: grid;
    grid-template-columns: 1fr 160px;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    align-items: start;
}

.material-form-search {
    display: flex;
    flex-direction: column;
}

.material-form-quantity {
    display: flex;
    flex-direction: column;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.2s;
    background: white;
}

.search-input:focus {
    border-color: #003859;
    box-shadow: 0 0 0 3px rgba(0, 56, 89, 0.1);
}

.search-input::placeholder {
    color: #9ca3af;
}

.quantity-input {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    font-weight: 600;
    text-align: center;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.2s;
    background: white;
}

.quantity-input:focus {
    border-color: #003859;
    box-shadow: 0 0 0 3px rgba(0, 56, 89, 0.1);
}

.quantity-input::-webkit-inner-spin-button,
.quantity-input::-webkit-outer-spin-button {
    opacity: 1;
    height: 1.5rem;
    cursor: pointer;
}

.btn-add-material {
    width: 100%;
    padding: 0.875rem 1.5rem;
    font-size: 0.9375rem;
    font-weight: 700;
    border-radius: 8px;
    transition: all 0.2s;
}

.btn-add-material:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.search-wrapper {
    position: relative;
    width: 100%;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
    margin-top: 0.25rem;
}

.search-result-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-item:hover {
    background-color: #f9fafb;
}

.search-result-item.no-results {
    color: #6b7280;
    cursor: default;
}

.search-result-item.no-results:hover {
    background-color: white;
}

.result-name {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.25rem;
}

.result-details {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.btn-select-material {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #374151;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-select-material:hover {
    border-color: #003859;
    box-shadow: 0 0 0 3px rgba(0, 56, 89, 0.1);
}

.btn-select-material .placeholder-text {
    color: #9ca3af;
}

.btn-select-arrow {
    color: #6b7280;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 2rem;
    animation: fadeIn 0.2s;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.material-search-modal {
    max-width: 700px;
    width: 100%;
    max-height: 85vh;
}

.modal-container {
    background: white;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.3s;
}

@keyframes slideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #003859;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.modal-close-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: #f3f4f6;
    border-radius: 8px;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: "Jost", sans-serif;
}

.modal-close-btn:hover {
    background: #e5e7eb;
    color: #374151;
}

.modal-body {
    padding: 2rem;
    overflow-y: auto;
    flex: 1;
}

.search-input-wrapper {
    margin-bottom: 1.5rem;
}

.search-input-full {
    width: 100%;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.2s;
    background: white;
}

.search-input-full:focus {
    border-color: #003859;
    box-shadow: 0 0 0 3px rgba(0, 56, 89, 0.1);
    outline: none;
}

.search-loading {
    margin-top: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.search-results-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-height: 60vh;
    overflow-y: auto;
}

.search-result-item-modal {
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.search-result-item-modal:hover {
    border-color: #003859;
    background: #f9fafb;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.result-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
    gap: 1rem;
}

.result-name {
    font-weight: 700;
    color: #003859;
    font-size: 1rem;
    flex: 1;
}

.result-article {
    font-size: 0.875rem;
    color: #6b7280;
    white-space: nowrap;
}

.result-footer {
    display: flex;
    gap: 1rem;
    align-items: center;
    font-size: 0.875rem;
    color: #6b7280;
    flex-wrap: wrap;
}

.result-category {
    padding: 0.25rem 0.5rem;
    background: #f3f4f6;
    border-radius: 4px;
}

.result-available {
    color: #059669;
    font-weight: 600;
}

.result-price {
    margin-left: auto;
    color: #003859;
    font-weight: 700;
    font-size: 1rem;
}

.no-results-message {
    text-align: center;
    padding: 3rem 2rem;
    color: #6b7280;
    font-size: 0.9375rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-input {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #046490;
    box-shadow: 0 0 0 3px rgba(4, 100, 144, 0.1);
}

.btn-primary {
    padding: 0.75rem 1.5rem;
    background: #046490;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary:hover:not(:disabled) {
    background: #003859;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-add-work,
.btn-add-material {
    width: 100%;
    margin-top: 0.5rem;
}

.btn-delete {
    padding: 0.375rem 0.75rem;
    background: #fee2e2;
    color: #991b1b;
    border: none;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-delete:hover:not(:disabled) {
    background: #fecaca;
}

.btn-delete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-in-work {
    padding: 0.75rem 1.5rem;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-in-work:hover:not(:disabled) {
    background: #2563eb;
}

.btn-in-work:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #9ca3af;
}

.btn-waiting-parts {
    padding: 0.75rem 1.5rem;
    background: #f59e0b;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-waiting-parts:hover:not(:disabled) {
    background: #d97706;
}

.btn-waiting-parts:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #9ca3af;
}

.btn-complete {
    padding: 0.75rem 1.5rem;
    background: #059669;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-complete:hover:not(:disabled) {
    background: #047857;
}

.btn-complete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #9ca3af;
}

.loading,
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.error-state {
    text-align: center;
    padding: 3rem;
    color: #dc2626;
}

.error-state p {
    margin-bottom: 1rem;
    font-size: 1.125rem;
}

.comments-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #e5e7eb;
}

.comments-list {
    margin-bottom: 1.5rem;
}

.comment-item {
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.comment-content {
    color: #374151;
    line-height: 1.6;
    white-space: pre-wrap;
}

.comment-form {
    margin-top: 1.5rem;
}

.form-textarea {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    font-family: inherit;
    transition: all 0.2s;
    resize: vertical;
    min-height: 80px;
}

.form-textarea:focus {
    outline: none;
    border-color: #046490;
    box-shadow: 0 0 0 3px rgba(4, 100, 144, 0.1);
}

.btn-save-comment {
    margin-top: 0.75rem;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .pos-page-content {
        padding: 0.75rem;
        border-radius: 8px;
    }

    .order-content {
        gap: 0.75rem;
    }

    .order-info-section {
        padding: 0.75rem;
        border-radius: 8px;
    }

    .order-header-section {
        padding: 1rem;
    }

    .order-header-top {
        flex-direction: column;
        align-items: stretch;
    }

    .order-title {
        font-size: 1.25rem;
    }

    .status-actions {
        flex-direction: column;
    }

    .status-actions .btn-status {
        width: 100%;
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }

    .order-info-section {
        padding: 1rem;
    }

    .section-title {
        font-size: 1.125rem;
        margin-bottom: 1rem;
    }

    .order-info-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .info-card {
        padding: 0.75rem;
    }

    .info-item {
        padding: 0.5rem 0;
    }

    .info-label {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 0.8125rem;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .form-row-inline {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }

    .material-form-row {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .form-group {
        margin-bottom: 0.75rem;
    }

    .form-label {
        font-size: 0.8125rem;
        margin-bottom: 0.375rem;
    }

    .form-input,
    .form-select,
    .form-textarea {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
    }

    .form-group-price,
    .form-group-quantity {
        min-width: auto;
        max-width: none;
    }

    .works-section,
    .materials-section {
        padding: 0.75rem;
        border-radius: 8px;
    }

    .section-title {
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }

    .work-item,
    .material-item {
        padding: 0.75rem;
        border-radius: 6px;
    }

    .item-header {
        margin-bottom: 0.5rem;
    }

    .item-name {
        font-size: 0.875rem;
    }

    .item-details {
        font-size: 0.75rem;
        gap: 0.25rem;
    }

    .btn-remove {
        padding: 0.375rem 0.5rem;
        font-size: 0.75rem;
    }

    .btn-add {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }

    .btn-save-comment {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
        margin-top: 0.5rem;
    }
}
</style>
