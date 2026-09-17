<script>
import {
    orderService,
    statusLabel,
    allowedTransitions,
    BILLING_LABELS,
    URGENCY_LABELS,
    KIND_LABELS,
} from "../../services/OrderService.js";
import { actorService } from "../../services/ActorService.js";
import { equipmentService } from "../../services/EquipmentService.js";
import { financeService } from "../../services/FinanceService.js";
import { workshopService } from "../../services/WorkshopService.js";

function emptySharpening() {
    return { kind: "sharpening", title: "", quantity: 1 };
}

function emptyRepair() {
    return { kind: "repair", equipment_id: "", problem: "" };
}

export default {
    name: "OrderShowPage",
    data() {
        return {
            order: null,
            client: null,
            masters: [],
            equipments: [],
            masterId: "",
            editItems: [],
            workshopJob: null,
            pricing: null,
            priceDrafts: {},
            loading: false,
            saving: false,
            savingPricing: false,
            error: null,
            statusLabel,
            BILLING_LABELS,
            URGENCY_LABELS,
            KIND_LABELS,
        };
    },
    computed: {
        canEditItems() {
            return this.order?.status === "created";
        },
        canAssignMaster() {
            return this.order?.status === "created";
        },
        isWorksCompleted() {
            return this.order?.status === "works_completed";
        },
        showPricing() {
            return ["works_completed", "ready", "issued"].includes(
                this.order?.status,
            );
        },
        canEditPricing() {
            return this.order?.status === "works_completed";
        },
        showItemsSection() {
            return this.canEditItems || !this.showPricing;
        },
        pricingComplete() {
            if (!this.workshopJob?.items?.length) {
                return false;
            }
            const lines = this.pricing?.lines || [];
            return this.workshopJob.items.every((jobItem) => {
                const line = lines.find(
                    (row) =>
                        Number(row.order_item_id) ===
                        Number(jobItem.order_item_id),
                );
                if (!line) {
                    return false;
                }
                const amount = Number(line.amount);
                return !Number.isNaN(amount) && amount >= 0;
            });
        },
        pricingTotal() {
            if (this.pricing?.total != null) {
                return this.pricing.total;
            }
            return "0.00";
        },
        clientLabel() {
            if (this.client?.name) {
                return this.client.name;
            }
            if (this.client?.email) {
                return this.client.email;
            }
            return this.order?.client_id
                ? `Клиент #${this.order.client_id}`
                : "—";
        },
        nextStatuses() {
            const statuses = allowedTransitions(this.order?.status);
            if (this.order?.status === "works_completed" && !this.pricingComplete) {
                return statuses.filter((s) => s !== "ready");
            }
            return statuses;
        },
    },
    async mounted() {
        await Promise.all([this.loadMasters(), this.load()]);
    },
    methods: {
        async loadMasters() {
            try {
                this.masters = await actorService.list("masters");
            } catch {
                this.masters = [];
            }
        },
        async loadClient(clientId) {
            if (!clientId) {
                this.client = null;
                return;
            }
            try {
                this.client = await actorService.get("clients", clientId);
            } catch {
                this.client = null;
            }
        },
        async loadEquipments(clientId) {
            try {
                this.equipments = await equipmentService.list(clientId);
            } catch {
                this.equipments = [];
            }
        },
        orderItem(orderItemId) {
            const id = Number(orderItemId);
            return (this.order?.items || []).find(
                (item) => Number(item.id) === id,
            );
        },
        positionTitle(jobItem) {
            const item = this.orderItem(jobItem.order_item_id);
            if (!item) {
                return "Позиция";
            }
            if (item.kind === "sharpening") {
                return item.title || KIND_LABELS.sharpening;
            }
            return this.equipmentLabel(item.equipment_id);
        },
        syncPriceDrafts() {
            const drafts = {};
            for (const jobItem of this.workshopJob?.items || []) {
                const line = (this.pricing?.lines || []).find(
                    (row) =>
                        Number(row.order_item_id) ===
                        Number(jobItem.order_item_id),
                );
                drafts[jobItem.order_item_id] =
                    line?.amount != null ? String(line.amount) : "";
            }
            this.priceDrafts = drafts;
        },
        async loadWorkshopAndPricing() {
            this.workshopJob = null;
            this.pricing = null;
            this.priceDrafts = {};
            if (!this.showPricing || !this.order) {
                return;
            }
            try {
                this.workshopJob = await workshopService.getByOrder(this.order.id);
            } catch (e) {
                this.workshopJob = null;
                if (e.response?.status !== 404) {
                    this.error =
                        e.response?.data?.message ||
                        "Не удалось загрузить работы мастера";
                }
            }
            try {
                this.pricing = await financeService.getByOrder(this.order.id);
            } catch (e) {
                if (e.response?.status !== 404) {
                    throw e;
                }
                this.pricing = null;
            }
            this.syncPriceDrafts();
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.order = await orderService.get(this.$route.params.id);
                this.masterId = this.order.master_id
                    ? String(this.order.master_id)
                    : "";
                this.syncEditItems();
                await Promise.all([
                    this.loadClient(this.order.client_id),
                    this.loadEquipments(this.order.client_id),
                    this.loadWorkshopAndPricing(),
                ]);
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить заказ";
                this.order = null;
            } finally {
                this.loading = false;
            }
        },
        syncEditItems() {
            this.editItems = (this.order.items || []).map((item) => {
                if (item.kind === "sharpening") {
                    return {
                        kind: "sharpening",
                        title: item.title || "",
                        quantity: item.quantity || 1,
                    };
                }
                return {
                    kind: "repair",
                    equipment_id: item.equipment_id
                        ? String(item.equipment_id)
                        : "",
                    problem: item.problem || "",
                };
            });
            if (this.editItems.length === 0) {
                this.editItems = [emptySharpening()];
            }
        },
        addSharpening() {
            this.editItems.push(emptySharpening());
        },
        addRepair() {
            this.editItems.push(emptyRepair());
        },
        removeItem(index) {
            this.editItems.splice(index, 1);
            if (this.editItems.length === 0) {
                this.editItems.push(emptySharpening());
            }
        },
        buildItemsPayload() {
            return this.editItems.map((item) => {
                if (item.kind === "sharpening") {
                    return {
                        kind: "sharpening",
                        title: item.title,
                        quantity: Number(item.quantity),
                    };
                }
                return {
                    kind: "repair",
                    equipment_id: Number(item.equipment_id),
                    problem: item.problem || null,
                };
            });
        },
        async saveItems() {
            this.saving = true;
            this.error = null;
            try {
                this.order = await orderService.updateItems(
                    this.order.id,
                    this.buildItemsPayload(),
                );
                this.syncEditItems();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось сохранить предметы";
            } finally {
                this.saving = false;
            }
        },
        async assignMaster() {
            if (!this.masterId) {
                this.error = "Выберите мастера";
                return;
            }
            this.saving = true;
            this.error = null;
            try {
                this.order = await orderService.assignMaster(
                    this.order.id,
                    Number(this.masterId),
                );
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось назначить мастера";
            } finally {
                this.saving = false;
            }
        },
        async savePricing() {
            if (!this.workshopJob?.items?.length) {
                this.error = "Нет позиций workshop для калькуляции";
                return;
            }
            const lines = [];
            for (const jobItem of this.workshopJob.items) {
                const raw = this.priceDrafts[jobItem.order_item_id];
                if (raw === "" || raw == null) {
                    this.error = "Укажите цену по каждой позиции";
                    return;
                }
                lines.push({
                    order_item_id: jobItem.order_item_id,
                    amount: Number(raw),
                });
            }
            this.savingPricing = true;
            this.error = null;
            try {
                this.pricing = await financeService.upsertByOrder(
                    this.order.id,
                    lines,
                );
                this.syncPriceDrafts();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось сохранить цены";
            } finally {
                this.savingPricing = false;
            }
        },
        async transition(status) {
            if (status === "ready" && !this.pricingComplete) {
                this.error = "Сначала укажите цены по всем позициям";
                return;
            }
            this.saving = true;
            this.error = null;
            try {
                this.order = await orderService.transition(this.order.id, status);
                this.syncEditItems();
                await this.loadWorkshopAndPricing();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось сменить статус";
            } finally {
                this.saving = false;
            }
        },
        masterName(id) {
            if (!id) return "—";
            const master = this.masters.find((m) => m.id === id);
            return master?.name || master?.email || `#${id}`;
        },
        equipmentLabel(id) {
            const eq = this.equipments.find((e) => e.id === id);
            return eq ? `${eq.name} · ${eq.brand}` : `#${id}`;
        },
        back() {
            this.$router.push({ name: "manager.orders" });
        },
    },
};
</script>

<template>
    <div class="w-full space-y-4 sm:space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-jost-bold text-dark-blue-500">
                Заказ #{{ $route.params.id }}
            </h1>
            <button
                type="button"
                class="border border-slate-300 px-4 py-2 text-sm text-slate-600"
                @click="back"
            >
                К списку
            </button>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <template v-if="order && !loading">
            <div class="space-y-1 border border-slate-200 bg-white p-4 text-sm">
                <p>
                    <span class="text-slate-500">Статус:</span>
                    {{ statusLabel(order.status) }}
                </p>
                <p>
                    <span class="text-slate-500">Клиент:</span>
                    {{ clientLabel }}
                </p>
                <p>
                    <span class="text-slate-500">Мастер:</span>
                    {{ masterName(order.master_id) }}
                </p>
                <p>
                    <span class="text-slate-500">Ориентир:</span>
                    {{ order.estimated_cost }}
                </p>
                <p v-if="showPricing">
                    <span class="text-slate-500">Итого:</span>
                    {{ pricingTotal }}
                </p>
                <p class="text-xs text-slate-400">
                    {{ BILLING_LABELS[order.billing_type] || order.billing_type }}
                    ·
                    {{ URGENCY_LABELS[order.urgency] || order.urgency }}
                    <template v-if="order.needs_delivery">
                        · доставка {{ order.delivery_address || "—" }}
                    </template>
                </p>
                <p v-if="order.review" class="text-xs text-slate-500">
                    Отзыв: {{ order.review.rating }}/5
                    <span v-if="order.review.text">— {{ order.review.text }}</span>
                </p>
            </div>

            <!-- created: состав + мастер -->
            <section v-if="canEditItems" class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-jost-bold text-dark-blue-500">
                        Состав заказа
                    </h2>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            class="text-sm text-pink-600 hover:underline"
                            @click="addSharpening"
                        >
                            + заточка
                        </button>
                        <button
                            type="button"
                            class="text-sm text-pink-600 hover:underline"
                            @click="addRepair"
                        >
                            + ремонт
                        </button>
                    </div>
                </div>

                <div
                    v-for="(item, index) in editItems"
                    :key="index"
                    class="space-y-3 border border-slate-200 p-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-jost-medium">
                            {{ KIND_LABELS[item.kind] }}
                        </span>
                        <button
                            type="button"
                            class="text-sm text-red-600 hover:underline"
                            @click="removeItem(index)"
                        >
                            Убрать
                        </button>
                    </div>
                    <template v-if="item.kind === 'sharpening'">
                        <input
                            v-model="item.title"
                            type="text"
                            placeholder="Название"
                            class="w-full border border-slate-300 px-3 py-2"
                        />
                        <input
                            v-model.number="item.quantity"
                            type="number"
                            min="1"
                            class="w-full border border-slate-300 px-3 py-2"
                        />
                    </template>
                    <template v-else>
                        <select
                            v-model="item.equipment_id"
                            class="w-full border border-slate-300 px-3 py-2"
                        >
                            <option value="" disabled>Оборудование</option>
                            <option
                                v-for="eq in equipments"
                                :key="eq.id"
                                :value="String(eq.id)"
                            >
                                {{ eq.name }} · {{ eq.brand }}
                            </option>
                        </select>
                        <input
                            v-model="item.problem"
                            type="text"
                            placeholder="Проблема"
                            class="w-full border border-slate-300 px-3 py-2"
                        />
                    </template>
                </div>
                <button
                    type="button"
                    class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                    :disabled="saving"
                    @click="saveItems"
                >
                    Сохранить предметы
                </button>
            </section>

            <section v-if="canAssignMaster" class="space-y-3">
                <h2 class="text-lg font-jost-bold text-dark-blue-500">
                    Назначить мастера
                </h2>
                <div class="flex flex-wrap gap-3">
                    <select
                        v-model="masterId"
                        class="min-w-[12rem] flex-1 border border-slate-300 px-3 py-2"
                    >
                        <option value="" disabled>Выберите мастера</option>
                        <option
                            v-for="master in masters"
                            :key="master.id"
                            :value="String(master.id)"
                        >
                            {{ master.name || master.email || `#${master.id}` }}
                        </option>
                    </select>
                    <button
                        type="button"
                        class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                        :disabled="saving"
                        @click="assignMaster"
                    >
                        Назначить
                    </button>
                </div>
            </section>

            <!-- works_completed / ready / issued: калькуляция как основной блок -->
            <section v-if="showPricing" class="space-y-4">
                <h2 class="text-lg font-jost-bold text-dark-blue-500">
                    {{ isWorksCompleted ? "Калькуляция" : "Состав и цены" }}
                </h2>
                <p
                    v-if="isWorksCompleted && !pricingComplete"
                    class="text-sm text-slate-500"
                >
                    Укажите цену по каждой позиции — без этого нельзя перевести в
                    «Готов».
                </p>
                <p v-if="!workshopJob" class="text-sm text-slate-500">
                    Workshop job по заказу не найден.
                </p>
                <div
                    v-for="jobItem in workshopJob?.items || []"
                    :key="jobItem.order_item_id"
                    class="space-y-3 border border-slate-200 bg-white p-4"
                >
                    <div class="text-sm font-jost-medium text-dark-blue-500">
                        {{
                            KIND_LABELS[orderItem(jobItem.order_item_id)?.kind] ||
                            "Позиция"
                        }}
                        ·
                        {{ positionTitle(jobItem) }}
                    </div>
                    <p
                        v-if="orderItem(jobItem.order_item_id)?.kind === 'sharpening'"
                        class="text-sm text-slate-600"
                    >
                        Заявлено
                        {{ orderItem(jobItem.order_item_id)?.quantity ?? "—" }}
                        <span v-if="jobItem.completed_qty != null">
                            · заточено {{ jobItem.completed_qty }}
                        </span>
                    </p>
                    <p
                        v-else-if="orderItem(jobItem.order_item_id)?.kind === 'repair'"
                        class="text-sm text-slate-600"
                    >
                        <span v-if="orderItem(jobItem.order_item_id)?.problem">
                            {{ orderItem(jobItem.order_item_id).problem }}
                        </span>
                    </p>
                    <div class="space-y-1">
                        <div class="text-sm text-slate-500">Работы мастера</div>
                        <ul
                            v-if="(jobItem.works || []).length"
                            class="list-disc space-y-1 pl-5 text-sm text-slate-700"
                        >
                            <li
                                v-for="work in jobItem.works"
                                :key="work.id || work.title + String(work.position)"
                            >
                                {{ work.title }}
                            </li>
                        </ul>
                        <p v-else class="text-sm text-slate-400">Работы не указаны</p>
                    </div>
                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">Цена позиции</span>
                        <input
                            v-model="priceDrafts[jobItem.order_item_id]"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full border border-slate-300 px-3 py-2"
                            :disabled="!canEditPricing"
                        />
                    </label>
                </div>
                <div
                    v-if="workshopJob?.items?.length"
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3 text-sm"
                >
                    <span class="font-jost-medium text-dark-blue-500">
                        Итого: {{ pricingTotal }}
                    </span>
                    <button
                        v-if="canEditPricing"
                        type="button"
                        class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                        :disabled="savingPricing"
                        @click="savePricing"
                    >
                        {{ savingPricing ? "Сохраняю…" : "Сохранить цены" }}
                    </button>
                </div>
            </section>

            <!-- предметы только когда нет калькуляции (in_progress и т.п.) -->
            <section
                v-if="showItemsSection && !canEditItems"
                class="space-y-3"
            >
                <h2 class="text-lg font-jost-bold text-dark-blue-500">
                    Предметы
                </h2>
                <ul class="divide-y divide-slate-100 border border-slate-200">
                    <li
                        v-for="item in order.items"
                        :key="item.id"
                        class="px-4 py-3 text-sm"
                    >
                        <div class="font-jost-medium text-dark-blue-500">
                            {{ KIND_LABELS[item.kind] || item.kind }}
                        </div>
                        <div v-if="item.kind === 'sharpening'" class="text-slate-600">
                            {{ item.title }} × {{ item.quantity }}
                        </div>
                        <div v-else class="text-slate-600">
                            {{ equipmentLabel(item.equipment_id) }}
                            <span v-if="item.problem">· {{ item.problem }}</span>
                        </div>
                    </li>
                </ul>
            </section>

            <section v-if="nextStatuses.length" class="space-y-3">
                <h2 class="text-lg font-jost-bold text-dark-blue-500">
                    Дальше
                </h2>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="s in nextStatuses"
                        :key="s"
                        type="button"
                        class="border border-pink-500 px-3 py-1.5 text-sm text-pink-600 hover:bg-pink-50 disabled:opacity-60"
                        :disabled="saving"
                        @click="transition(s)"
                    >
                        → {{ statusLabel(s) }}
                    </button>
                </div>
            </section>
        </template>
    </div>
</template>
