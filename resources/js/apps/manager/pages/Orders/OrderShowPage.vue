<script>
import { formatOrderDate } from "../../../../shared/formatOrderDate.js";
import { actorService } from "../../services/ActorService.js";
import { documentService } from "../../services/DocumentService.js";
import { equipmentService } from "../../services/EquipmentService.js";
import { financeService } from "../../services/FinanceService.js";
import {
    allowedTransitions,
    BILLING_LABELS,
    KIND_LABELS,
    orderService,
    statusLabel,
    transitionActions,
    URGENCY_LABELS,
} from "../../services/OrderService.js";
import { warehouseService } from "../../services/WarehouseService.js";
import { workshopService } from "../../services/WorkshopService.js";

function emptySharpening() {
    return { kind: "sharpening", title: "", quantity: 1 };
}

function emptyRepair() {
    return { kind: "repair", equipment_id: "", problem: "" };
}

function emptyMaterial() {
    return { stock_item_id: "", qty: "", amount: "" };
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
            stockItems: [],
            orderIssue: null,
            materialDrafts: [],
            loading: false,
            saving: false,
            savingPricing: false,
            savingMaterials: false,
            error: null,
            documentError: null,
            openingDocument: false,
            commentBody: "",
            savingComment: false,
            commentError: null,
            resolveBody: "",
            statusLabel,
            formatOrderDate,
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
        canPrintReceipt() {
            return this.order && this.order.status !== "cancelled";
        },
        canPrintHandoverAct() {
            return ["ready", "issued"].includes(this.order?.status);
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
            const works = this.allWorks();
            if (works.length === 0) {
                return true;
            }
            const lines = this.pricing?.lines || [];
            return works.every((work) => {
                const line = lines.find(
                    (row) => Number(row.work_entry_id) === Number(work.id),
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
            if (
                this.order?.status === "works_completed" &&
                !this.pricingComplete
            ) {
                return statuses.filter((s) => s !== "ready");
            }
            return statuses;
        },
        nextActions() {
            const allowed = new Set(this.nextStatuses);
            return transitionActions(this.order?.status).filter((action) =>
                allowed.has(action.to),
            );
        },
        readyBlockedByPricing() {
            return (
                this.order?.status === "works_completed" &&
                !this.pricingComplete
            );
        },
        isOnApproval() {
            return this.order?.status === "approval";
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
        commentAuthorLabel(comment) {
            if (comment?.author_type === "managers") {
                return `Менеджер #${comment.author_id}`;
            }
            if (comment?.author_type === "masters") {
                return `Мастер #${comment.author_id}`;
            }
            return `#${comment?.author_id || "—"}`;
        },
        commentKindLabel(kind) {
            switch (kind) {
                case "approval_request":
                    return "На согласование";
                case "approval_result":
                    return "Результат согласования";
                default:
                    return null;
            }
        },
        commentKindClass(kind) {
            switch (kind) {
                case "approval_request":
                    return "border-amber-200 bg-amber-50/60";
                case "approval_result":
                    return "border-emerald-200 bg-emerald-50/60";
                default:
                    return "border-slate-100 bg-slate-50/40";
            }
        },
        scrollCommentsToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.commentsList;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        },
        async submitComment() {
            const body = String(this.commentBody || "").trim();
            if (!this.order?.id || !body) {
                return;
            }
            this.savingComment = true;
            this.commentError = null;
            try {
                this.order = await orderService.addComment(this.order.id, body);
                this.commentBody = "";
                this.scrollCommentsToBottom();
            } catch (e) {
                this.commentError =
                    e.response?.data?.message ||
                    "Не удалось отправить комментарий";
            } finally {
                this.savingComment = false;
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
            for (const work of this.allWorks()) {
                const line = (this.pricing?.lines || []).find(
                    (row) => Number(row.work_entry_id) === Number(work.id),
                );
                drafts[work.id] =
                    line?.amount != null ? String(line.amount) : "";
            }
            this.priceDrafts = drafts;
        },
        allWorks() {
            const works = [];
            for (const jobItem of this.workshopJob?.items || []) {
                for (const work of jobItem.works || []) {
                    if (work?.id != null) {
                        works.push(work);
                    }
                }
            }
            return works;
        },
        syncMaterialDrafts() {
            const amounts = {};
            for (const line of this.pricing?.material_lines || []) {
                amounts[Number(line.stock_item_id)] = String(line.amount);
            }
            const issueLines = this.orderIssue?.lines || [];
            if (issueLines.length === 0) {
                this.materialDrafts = [];
                return;
            }
            this.materialDrafts = issueLines.map((line) => ({
                stock_item_id: String(line.stock_item_id),
                qty: String(line.qty),
                amount: amounts[Number(line.stock_item_id)] ?? "",
            }));
        },
        stockItemLabel(id) {
            const item = this.stockItems.find(
                (row) => Number(row.id) === Number(id),
            );
            if (!item) {
                return `#${id}`;
            }
            return `${item.name} · ${item.qty_on_hand} ${item.unit}`;
        },
        async loadStockItems() {
            try {
                this.stockItems = await warehouseService.list();
            } catch {
                this.stockItems = [];
            }
        },
        async loadWorkshopAndPricing() {
            this.workshopJob = null;
            this.pricing = null;
            this.priceDrafts = {};
            this.orderIssue = null;
            this.materialDrafts = [];
            if (!this.showPricing || !this.order) {
                return;
            }
            await this.loadStockItems();
            try {
                this.workshopJob = await workshopService.getByOrder(
                    this.order.id,
                );
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
            try {
                this.orderIssue = await warehouseService.getIssueByOrder(
                    this.order.id,
                );
            } catch (e) {
                this.orderIssue = null;
                if (e.response?.status !== 404) {
                    this.error =
                        e.response?.data?.message ||
                        "Не удалось загрузить списание склада";
                }
            }
            this.syncPriceDrafts();
            this.syncMaterialDrafts();
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
                this.scrollCommentsToBottom();
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
                    e.response?.data?.message ||
                    "Не удалось сохранить предметы";
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
            const works = this.allWorks();
            const lines = [];
            for (const work of works) {
                const raw = this.priceDrafts[work.id];
                if (raw === "" || raw == null) {
                    this.error = "Укажите цену по каждой работе";
                    return;
                }
                lines.push({
                    work_entry_id: work.id,
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
        addMaterial() {
            this.materialDrafts.push(emptyMaterial());
        },
        removeMaterial(index) {
            this.materialDrafts.splice(index, 1);
        },
        async saveMaterials() {
            const lines = [];
            for (const row of this.materialDrafts) {
                if (!row.stock_item_id) {
                    this.error = "Выберите позицию склада";
                    return;
                }
                if (row.qty === "" || row.qty == null || Number(row.qty) <= 0) {
                    this.error = "Укажите qty > 0 по каждой строке материалов";
                    return;
                }
                if (row.amount === "" || row.amount == null) {
                    this.error = "Укажите цену по каждой строке материалов";
                    return;
                }
                lines.push({
                    stock_item_id: Number(row.stock_item_id),
                    qty: Number(row.qty),
                    amount: Number(row.amount),
                });
            }
            this.savingMaterials = true;
            this.error = null;
            try {
                const result = await warehouseService.applyOrderMaterials(
                    this.order.id,
                    lines,
                );
                this.orderIssue = result.issue;
                this.pricing = result.pricing;
                this.syncPriceDrafts();
                this.syncMaterialDrafts();
                await this.loadStockItems();
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    "Не удалось сохранить материалы";
            } finally {
                this.savingMaterials = false;
            }
        },
        async openDocument(type) {
            this.documentError = null;
            this.openingDocument = true;
            // open sync — иначе Safari/Chrome режут попап после await
            const previewWindow = window.open("about:blank", "_blank");
            try {
                await documentService.openOrderDocument(
                    this.order.id,
                    type,
                    previewWindow,
                );
            } catch (e) {
                this.documentError =
                    e.response?.data?.message ||
                    e.message ||
                    "Не удалось открыть документ";
            } finally {
                this.openingDocument = false;
            }
        },
        async transition(status) {
            if (status === "ready" && !this.pricingComplete) {
                this.error = "Сначала укажите цены по всем работам";
                return;
            }
            this.saving = true;
            this.error = null;
            try {
                this.order = await orderService.transition(
                    this.order.id,
                    status,
                );
                this.syncEditItems();
                await this.loadWorkshopAndPricing();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось сменить статус";
            } finally {
                this.saving = false;
            }
        },
        actionButtonClass(action) {
            const compact =
                "!min-h-8 !w-auto shrink-0 !px-2.5 !py-1.5 !text-sm";
            switch (action.tone) {
                case "forward":
                    return `app-btn-primary ${compact}`;
                case "warning":
                    return `app-btn-warning ${compact}`;
                case "danger":
                    return `app-btn-danger ${compact}`;
                case "neutral":
                    return `app-btn-secondary ${compact}`;
                default: {
                    const _exhaustive = action.tone;
                    void _exhaustive;
                    return `app-btn-secondary ${compact}`;
                }
            }
        },
        async runTransitionAction(action) {
            if (action.confirm && !window.confirm(action.confirm)) {
                return;
            }
            if (this.isOnApproval) {
                const body = String(this.resolveBody || "").trim();
                if (!body) {
                    this.error =
                        "Укажите результат согласования перед сменой статуса";
                    return;
                }
                this.saving = true;
                this.error = null;
                try {
                    this.order = await orderService.resolveApproval(
                        this.order.id,
                        action.to,
                        body,
                    );
                    this.resolveBody = "";
                    this.syncEditItems();
                    await this.loadWorkshopAndPricing();
                    this.scrollCommentsToBottom();
                } catch (e) {
                    this.error =
                        e.response?.data?.message ||
                        "Не удалось завершить согласование";
                } finally {
                    this.saving = false;
                }
                return;
            }
            await this.transition(action.to);
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
        moduleLabel(orderItemId, moduleId) {
            if (moduleId == null) {
                return null;
            }
            const item = this.orderItem(orderItemId);
            if (!item?.equipment_id) {
                return `#${moduleId}`;
            }
            const eq = this.equipments.find(
                (e) => Number(e.id) === Number(item.equipment_id),
            );
            const module = (eq?.modules || []).find(
                (m) => Number(m.id) === Number(moduleId),
            );
            if (!module) {
                return `#${moduleId}`;
            }
            return `${module.name} (${module.serial_number})`;
        },
        back() {
            this.$router.push({ name: "manager.orders" });
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Заказ #{{ $route.params.id }}</h1>
            <button
                type="button"
                class="app-btn-ghost w-full sm:w-auto"
                @click="back"
            >
                К списку
            </button>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <template v-if="order && !loading">
            <div
                class="grid gap-4 lg:grid-cols-[minmax(16rem,20rem)_minmax(0,1fr)] lg:items-start lg:gap-6"
            >
                <aside class="space-y-3 lg:sticky lg:top-4">
                    <div
                        class="space-y-2 border border-slate-200 bg-white p-3 text-sm lg:p-4"
                    >
                        <p class="font-jost-medium text-dark-blue-500">
                            {{ statusLabel(order.status) }}
                        </p>
                        <dl class="space-y-1.5">
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Клиент</dt>
                                <dd class="text-right text-slate-800">
                                    {{ clientLabel }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Телефон</dt>
                                <dd class="text-right text-slate-800">
                                    {{ client?.phone || "—" }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Мастер</dt>
                                <dd class="text-right text-slate-800">
                                    {{ masterName(order.master_id) }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Создан</dt>
                                <dd class="text-right text-slate-800">
                                    {{ formatOrderDate(order.created_at) }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Выдан</dt>
                                <dd class="text-right text-slate-800">
                                    {{ formatOrderDate(order.issued_at) }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Ориентир</dt>
                                <dd class="text-right text-slate-800">
                                    {{ order.estimated_cost }} ₽
                                </dd>
                            </div>
                            <div
                                v-if="showPricing"
                                class="flex justify-between gap-2"
                            >
                                <dt class="text-slate-500">Итого</dt>
                                <dd
                                    class="text-right font-jost-medium text-dark-blue-500"
                                >
                                    {{ pricingTotal }} ₽
                                </dd>
                            </div>
                        </dl>
                        <p
                            class="border-t border-slate-100 pt-2 text-xs text-slate-500"
                        >
                            {{
                                BILLING_LABELS[order.billing_type] ||
                                order.billing_type
                            }}
                            ·
                            {{ URGENCY_LABELS[order.urgency] || order.urgency }}
                        </p>
                        <p
                            v-if="order.needs_delivery"
                            class="text-xs text-slate-500"
                        >
                            Доставка: {{ order.delivery_address || "—" }}
                        </p>
                        <p v-if="order.review" class="text-xs text-slate-500">
                            Отзыв: {{ order.review.rating }}/5
                            <span v-if="order.review.text">
                                — {{ order.review.text }}
                            </span>
                        </p>
                    </div>

                    <section
                        class="space-y-2 border border-slate-200 bg-white p-3 lg:p-4"
                    >
                        <h2 class="text-sm font-jost-bold text-dark-blue-500">
                            Комментарии
                        </h2>
                        <ul
                            v-if="(order.comments || []).length"
                            ref="commentsList"
                            class="max-h-48 space-y-2 overflow-y-auto text-sm"
                        >
                            <li
                                v-for="comment in order.comments"
                                :key="comment.id"
                                class="border px-2 py-2"
                                :class="commentKindClass(comment.kind)"
                            >
                                <p class="text-xs text-slate-500">
                                    {{ commentAuthorLabel(comment) }}
                                    ·
                                    {{ formatOrderDate(comment.created_at) }}
                                </p>
                                <p
                                    v-if="commentKindLabel(comment.kind)"
                                    class="mt-0.5 text-xs font-jost-medium"
                                    :class="
                                        comment.kind === 'approval_request'
                                            ? 'text-amber-800'
                                            : 'text-emerald-800'
                                    "
                                >
                                    {{ commentKindLabel(comment.kind) }}
                                </p>
                                <p
                                    class="mt-1 whitespace-pre-wrap text-slate-800"
                                >
                                    {{ comment.body }}
                                </p>
                            </li>
                        </ul>
                        <p v-else class="text-xs text-slate-500">
                            Пока нет комментариев
                        </p>
                        <p v-if="commentError" class="text-xs text-red-600">
                            {{ commentError }}
                        </p>
                        <div class="flex gap-2">
                            <input
                                v-model="commentBody"
                                type="text"
                                class="app-field min-w-0 flex-1"
                                placeholder="Написать комментарий…"
                                :disabled="savingComment"
                                @keydown.enter.prevent="submitComment"
                            />
                            <button
                                type="button"
                                class="app-btn-secondary !min-h-11 shrink-0 !px-3"
                                :disabled="savingComment || !commentBody.trim()"
                                @click="submitComment"
                            >
                                {{ savingComment ? "…" : "Отправить" }}
                            </button>
                        </div>
                    </section>

                    <section
                        v-if="canAssignMaster"
                        class="space-y-2 border border-slate-200 bg-white p-3 lg:p-4"
                    >
                        <h2 class="text-sm font-jost-bold text-dark-blue-500">
                            Назначить мастера
                        </h2>
                        <select v-model="masterId" class="app-field">
                            <option value="" disabled>Выберите мастера</option>
                            <option
                                v-for="master in masters"
                                :key="master.id"
                                :value="String(master.id)"
                            >
                                {{
                                    master.name ||
                                    master.email ||
                                    `#${master.id}`
                                }}
                            </option>
                        </select>
                        <button
                            type="button"
                            class="app-btn-primary w-full"
                            :disabled="saving"
                            @click="assignMaster"
                        >
                            Назначить
                        </button>
                    </section>

                    <section
                        v-if="canPrintReceipt || canPrintHandoverAct"
                        class="space-y-2 border border-slate-200 bg-white p-3 lg:p-4"
                    >
                        <h2 class="text-sm font-jost-bold text-dark-blue-500">
                            Печать
                        </h2>
                        <p v-if="documentError" class="text-xs text-red-600">
                            {{ documentError }}
                        </p>
                        <div class="flex flex-col gap-2">
                            <button
                                v-if="canPrintReceipt"
                                type="button"
                                class="app-btn-secondary app-action-btn"
                                :disabled="openingDocument"
                                @click="openDocument('receipt')"
                            >
                                <span class="app-action-btn-title">
                                    Квитанция о приёме
                                </span>
                                <span class="app-action-btn-hint">
                                    PDF для печати при приёме
                                </span>
                            </button>
                            <button
                                v-if="canPrintHandoverAct"
                                type="button"
                                class="app-btn-secondary app-action-btn"
                                :disabled="openingDocument"
                                @click="openDocument('handover_act')"
                            >
                                <span class="app-action-btn-title">
                                    Акт выдачи
                                </span>
                                <span class="app-action-btn-hint">
                                    PDF для печати при выдаче
                                </span>
                            </button>
                        </div>
                    </section>

                </aside>

                <div class="min-w-0 space-y-4">
                    <section
                        v-if="nextActions.length || readyBlockedByPricing"
                        class="space-y-1.5 border border-slate-300 bg-white px-2.5 py-2 shadow-sm"
                    >
                        <h2 class="text-xs font-jost-bold text-dark-blue-500">
                            Действия
                        </h2>
                        <p
                            v-if="readyBlockedByPricing"
                            class="text-xs text-slate-600"
                        >
                            Чтобы отметить готовым, сначала укажите цены по всем
                            работам.
                        </p>
                        <textarea
                            v-if="isOnApproval"
                            v-model="resolveBody"
                            rows="2"
                            class="app-field !py-1.5 !text-sm"
                            placeholder="Результат согласования с клиентом…"
                        />
                        <div class="flex flex-row flex-wrap gap-1.5">
                            <button
                                v-for="action in nextActions"
                                :key="action.to"
                                type="button"
                                :class="actionButtonClass(action)"
                                :disabled="saving"
                                :title="action.hint"
                                @click="runTransitionAction(action)"
                            >
                                {{ action.title }}
                            </button>
                        </div>
                    </section>

                    <section v-if="canEditItems" class="space-y-3">
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <h2
                                class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                            >
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
                            class="space-y-2 border border-slate-200 bg-white p-3"
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
                                <div class="grid gap-2 sm:grid-cols-[1fr_6rem]">
                                    <input
                                        v-model="item.title"
                                        type="text"
                                        placeholder="Название"
                                        class="app-field"
                                    />
                                    <input
                                        v-model.number="item.quantity"
                                        type="number"
                                        min="1"
                                        class="app-field"
                                    />
                                </div>
                            </template>
                            <template v-else>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <select
                                        v-model="item.equipment_id"
                                        class="app-field"
                                    >
                                        <option value="" disabled>
                                            Оборудование
                                        </option>
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
                                        class="app-field"
                                    />
                                </div>
                            </template>
                        </div>
                        <button
                            type="button"
                            class="app-btn-primary w-full sm:w-auto"
                            :disabled="saving"
                            @click="saveItems"
                        >
                            Сохранить предметы
                        </button>
                    </section>

                    <section v-if="showPricing" class="space-y-3">
                        <h2
                            class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                        >
                            {{
                                isWorksCompleted
                                    ? "Калькуляция"
                                    : "Состав и цены"
                            }}
                        </h2>
                        <p
                            v-if="isWorksCompleted && !pricingComplete"
                            class="text-sm text-slate-500"
                        >
                            Укажите цену по каждой работе — без этого нельзя
                            перевести в «Готов».
                        </p>
                        <p v-if="!workshopJob" class="text-sm text-slate-500">
                            Workshop job по заказу не найден.
                        </p>
                        <div
                            v-for="jobItem in workshopJob?.items || []"
                            :key="jobItem.order_item_id"
                            class="space-y-2 border border-slate-200 bg-white p-3"
                        >
                            <div class="min-w-0 space-y-1">
                                <div
                                    class="text-sm font-jost-medium text-dark-blue-500"
                                >
                                    {{
                                        KIND_LABELS[
                                            orderItem(jobItem.order_item_id)
                                                ?.kind
                                        ] || "Позиция"
                                    }}
                                    ·
                                    {{ positionTitle(jobItem) }}
                                </div>
                                <p
                                    v-if="
                                        orderItem(jobItem.order_item_id)
                                            ?.kind === 'sharpening'
                                    "
                                    class="text-xs text-slate-600"
                                >
                                    Заявлено
                                    {{
                                        orderItem(jobItem.order_item_id)
                                            ?.quantity ?? "—"
                                    }}
                                    <span v-if="jobItem.completed_qty != null">
                                        · заточено
                                        {{ jobItem.completed_qty }}
                                    </span>
                                </p>
                                <p
                                    v-else-if="
                                        orderItem(jobItem.order_item_id)
                                            ?.kind === 'repair' &&
                                        orderItem(jobItem.order_item_id)
                                            ?.problem
                                    "
                                    class="text-xs text-slate-600"
                                >
                                    {{
                                        orderItem(jobItem.order_item_id).problem
                                    }}
                                </p>
                            </div>

                            <p
                                v-if="!(jobItem.works || []).length"
                                class="text-sm text-slate-500"
                            >
                                Работ нет — цена не требуется
                            </p>

                            <div
                                v-for="work in jobItem.works || []"
                                :key="work.id"
                                class="flex flex-col gap-2 border-t border-slate-100 pt-2 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0 text-sm text-slate-700">
                                    <div
                                        v-if="
                                            moduleLabel(
                                                jobItem.order_item_id,
                                                work.equipment_module_id,
                                            )
                                        "
                                        class="text-xs text-slate-500"
                                    >
                                        Модуль:
                                        {{
                                            moduleLabel(
                                                jobItem.order_item_id,
                                                work.equipment_module_id,
                                            )
                                        }}
                                    </div>
                                    <div>{{ work.title || "Работа" }}</div>
                                </div>
                                <label
                                    class="block w-full shrink-0 space-y-1 sm:w-36"
                                >
                                    <span class="text-xs text-slate-500"
                                        >Цена</span
                                    >
                                    <input
                                        v-model="priceDrafts[work.id]"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="app-field"
                                        :disabled="!canEditPricing"
                                    />
                                </label>
                            </div>
                        </div>
                        <div
                            v-if="workshopJob?.items?.length"
                            class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-2 text-sm"
                        >
                            <span class="font-jost-medium text-dark-blue-500">
                                Итого: {{ pricingTotal }} ₽
                            </span>
                            <button
                                v-if="canEditPricing"
                                type="button"
                                class="app-btn-primary"
                                :disabled="savingPricing"
                                @click="savePricing"
                            >
                                {{
                                    savingPricing
                                        ? "Сохраняю…"
                                        : "Сохранить цены"
                                }}
                            </button>
                        </div>
                    </section>

                    <section v-if="showPricing" class="space-y-3">
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <h2
                                class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                            >
                                Материалы со склада
                            </h2>
                            <button
                                v-if="canEditPricing"
                                type="button"
                                class="text-sm text-pink-600 hover:underline"
                                @click="addMaterial"
                            >
                                + материал
                            </button>
                        </div>
                        <div
                            v-for="(row, index) in materialDrafts"
                            :key="index"
                            class="grid gap-2 border border-slate-200 bg-white p-3 sm:grid-cols-4"
                        >
                            <select
                                v-model="row.stock_item_id"
                                class="app-field sm:col-span-2"
                                :disabled="!canEditPricing"
                            >
                                <option value="" disabled>
                                    Позиция склада
                                </option>
                                <option
                                    v-for="item in stockItems"
                                    :key="item.id"
                                    :value="String(item.id)"
                                >
                                    {{ item.name }} · {{ item.qty_on_hand }}
                                    {{ item.unit }}
                                </option>
                            </select>
                            <input
                                v-model="row.qty"
                                type="number"
                                min="0.001"
                                step="0.001"
                                placeholder="Кол-во"
                                class="app-field"
                                :disabled="!canEditPricing"
                            />
                            <div class="flex gap-2">
                                <input
                                    v-model="row.amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="Цена"
                                    class="app-field min-w-0 flex-1"
                                    :disabled="!canEditPricing"
                                />
                                <button
                                    v-if="canEditPricing"
                                    type="button"
                                    class="shrink-0 text-sm text-red-600 hover:underline"
                                    @click="removeMaterial(index)"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                        <p
                            v-if="materialDrafts.length === 0"
                            class="text-sm text-slate-400"
                        >
                            Материалы не списаны
                        </p>
                        <button
                            v-if="canEditPricing"
                            type="button"
                            class="app-btn-primary w-full sm:w-auto"
                            :disabled="savingMaterials"
                            @click="saveMaterials"
                        >
                            {{
                                savingMaterials
                                    ? "Сохраняю…"
                                    : "Сохранить материалы"
                            }}
                        </button>
                    </section>

                    <section
                        v-if="showItemsSection && !canEditItems"
                        class="space-y-2"
                    >
                        <h2
                            class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                        >
                            Предметы
                        </h2>
                        <ul
                            class="divide-y divide-slate-100 border border-slate-200 bg-white"
                        >
                            <li
                                v-for="item in order.items"
                                :key="item.id"
                                class="px-3 py-2.5 text-sm"
                            >
                                <div
                                    class="font-jost-medium text-dark-blue-500"
                                >
                                    {{ KIND_LABELS[item.kind] || item.kind }}
                                </div>
                                <div
                                    v-if="item.kind === 'sharpening'"
                                    class="text-slate-600"
                                >
                                    {{ item.title }} × {{ item.quantity }}
                                </div>
                                <div v-else class="text-slate-600">
                                    {{ equipmentLabel(item.equipment_id) }}
                                    <span v-if="item.problem">
                                        · {{ item.problem }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
        </template>
    </div>
</template>
