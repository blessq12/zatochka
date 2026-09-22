<script>
import { formatOrderDate } from "../../../../shared/formatOrderDate.js";
import { equipmentService } from "../../services/EquipmentService.js";
import {
    KIND_LABELS,
    URGENCY_LABELS,
    orderService,
    statusLabel,
} from "../../services/OrderService.js";
import { workshopService } from "../../services/WorkshopService.js";

function emptyWork() {
    return { title: "", equipment_module_id: "" };
}

export default {
    name: "JobPage",
    data() {
        return {
            job: null,
            order: null,
            drafts: {},
            equipmentById: {},
            loading: false,
            saving: false,
            completing: false,
            error: null,
            commentBody: "",
            savingComment: false,
            commentError: null,
            approvalBody: "",
            requestingApproval: false,
            workTitlePresets: ["заточка", "полировка", "восстановление"],
            KIND_LABELS,
            URGENCY_LABELS,
            statusLabel,
            formatOrderDate,
        };
    },
    computed: {
        isOpen() {
            return this.job?.status === "open";
        },
        canEditWorks() {
            return this.isOpen && this.order?.status === "in_progress";
        },
        canRequestApproval() {
            return this.order?.status === "in_progress";
        },
        isOnApproval() {
            return this.order?.status === "approval";
        },
        jobItems() {
            return this.job?.items || [];
        },
        orderEquipments() {
            const seen = new Set();
            const result = [];
            for (const item of this.order?.items || []) {
                if (item.kind !== "repair" || !item.equipment_id) {
                    continue;
                }
                const id = Number(item.equipment_id);
                if (seen.has(id)) {
                    continue;
                }
                seen.add(id);
                const equipment = this.equipmentById[id] || null;
                const problems = (this.order?.items || [])
                    .filter(
                        (row) =>
                            row.kind === "repair" &&
                            Number(row.equipment_id) === id &&
                            row.problem,
                    )
                    .map((row) => row.problem);
                result.push({ id, equipment, problems });
            }
            return result;
        },
    },
    async mounted() {
        await this.load();
    },
    watch: {
        "$route.params.id"() {
            this.load();
        },
    },
    methods: {
        sameId(a, b) {
            return Number(a) === Number(b);
        },
        orderItem(orderItemId) {
            return (this.order?.items || []).find((item) =>
                this.sameId(item.id, orderItemId),
            );
        },
        draftKey(orderItemId) {
            return Number(orderItemId);
        },
        isRepair(orderItemId) {
            return this.orderItem(orderItemId)?.kind === "repair";
        },
        modulesFor(orderItemId) {
            const equipmentId = this.orderItem(orderItemId)?.equipment_id;
            if (!equipmentId) {
                return [];
            }
            return this.equipmentById[Number(equipmentId)]?.modules || [];
        },
        declaredQty(orderItemId) {
            const qty = this.orderItem(orderItemId)?.quantity;
            return qty == null ? null : Number(qty);
        },
        clampCompletedQty(orderItemId, raw) {
            if (raw === "" || raw == null) {
                return null;
            }
            let qty = Number(raw);
            if (Number.isNaN(qty) || qty < 0) {
                qty = 0;
            }
            const max = this.declaredQty(orderItemId);
            if (max != null && qty > max) {
                qty = max;
            }
            return qty;
        },
        syncDrafts() {
            const drafts = {};
            for (const jobItem of this.jobItems) {
                const key = this.draftKey(jobItem.order_item_id);
                drafts[key] = {
                    completed_qty:
                        jobItem.completed_qty != null
                            ? jobItem.completed_qty
                            : "",
                    works:
                        (jobItem.works || []).length > 0
                            ? jobItem.works.map((w) => ({
                                  title: w.title || "",
                                  equipment_module_id:
                                      w.equipment_module_id != null
                                          ? Number(w.equipment_module_id)
                                          : "",
                              }))
                            : [emptyWork()],
                };
            }
            this.drafts = drafts;
        },
        isWorkBlank(work) {
            const title = String(work?.title || "").trim();
            const moduleId = work?.equipment_module_id;
            const hasModule = moduleId != null && moduleId !== "";
            return title === "" && !hasModule;
        },
        isWorkComplete(orderItemId, work) {
            if (!this.isRepair(orderItemId)) {
                return String(work?.title || "").trim() !== "";
            }
            const moduleId = work?.equipment_module_id;
            return moduleId != null && moduleId !== "";
        },
        incompleteWorks(orderItemId) {
            const works = this.drafts[this.draftKey(orderItemId)]?.works || [];
            return works
                .filter(
                    (work) =>
                        !this.isWorkBlank(work) &&
                        !this.isWorkComplete(orderItemId, work),
                )
                .map((work) => ({
                    title: work.title || "",
                    equipment_module_id:
                        work.equipment_module_id != null &&
                        work.equipment_module_id !== ""
                            ? Number(work.equipment_module_id)
                            : "",
                }));
        },
        restoreIncompleteWorks(orderItemId, incomplete) {
            if (!incomplete.length) {
                return;
            }
            const key = this.draftKey(orderItemId);
            const draft = this.drafts[key];
            if (!draft) {
                return;
            }
            const saved = (draft.works || []).filter(
                (work) => !this.isWorkBlank(work),
            );
            draft.works = [...saved, ...incomplete, emptyWork()];
        },
        async loadEquipments() {
            const ids = [
                ...new Set(
                    (this.order?.items || [])
                        .filter(
                            (item) =>
                                item.kind === "repair" && item.equipment_id,
                        )
                        .map((item) => Number(item.equipment_id)),
                ),
            ];
            const map = { ...this.equipmentById };
            await Promise.all(
                ids.map(async (id) => {
                    if (map[id]) {
                        return;
                    }
                    try {
                        map[id] = await equipmentService.get(id);
                    } catch {
                        map[id] = { id, modules: [] };
                    }
                }),
            );
            this.equipmentById = map;
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
        async submitApproval() {
            const body = String(this.approvalBody || "").trim();
            if (!this.order?.id) {
                return;
            }
            if (!body) {
                this.error =
                    "Укажите, что нужно согласовать с клиентом";
                return;
            }
            if (
                !window.confirm(
                    "Отправить заказ на согласование менеджеру?",
                )
            ) {
                return;
            }
            this.requestingApproval = true;
            this.error = null;
            try {
                this.order = await orderService.requestApproval(
                    this.order.id,
                    body,
                );
                this.approvalBody = "";
                this.scrollCommentsToBottom();
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    "Не удалось отправить на согласование";
            } finally {
                this.requestingApproval = false;
            }
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.job = await workshopService.get(this.$route.params.id);
                this.order = await orderService.get(this.job.order_id);
                await this.loadEquipments();
                this.syncDrafts();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить задание";
                this.job = null;
                this.order = null;
            } finally {
                this.loading = false;
                this.scrollCommentsToBottom();
            }
        },
        addWork(orderItemId) {
            this.drafts[this.draftKey(orderItemId)].works.push(emptyWork());
        },
        applyWorkTitle(orderItemId, index, title) {
            if (!this.canEditWorks) {
                return;
            }
            const draft = this.drafts[this.draftKey(orderItemId)];
            if (!draft?.works?.[index]) {
                return;
            }
            draft.works[index].title = title;
            this.persistItem(orderItemId);
        },
        removeWork(orderItemId, index) {
            const works = this.drafts[this.draftKey(orderItemId)].works;
            works.splice(index, 1);
            if (works.length === 0) {
                works.push(emptyWork());
            }
            if (this.canEditWorks) {
                this.persistItem(orderItemId);
            }
        },
        onQtyBlur(orderItemId) {
            const key = this.draftKey(orderItemId);
            const draft = this.drafts[key];
            if (!draft) {
                return;
            }
            const clamped = this.clampCompletedQty(
                orderItemId,
                draft.completed_qty,
            );
            draft.completed_qty = clamped == null ? "" : clamped;
            this.persistItem(orderItemId);
        },
        onWorkBlur(orderItemId) {
            this.persistItem(orderItemId);
        },
        buildPayload(jobItem) {
            const orderItemId = this.draftKey(jobItem.order_item_id);
            const draft = this.drafts[orderItemId];
            const orderItem = this.orderItem(orderItemId);
            const isRepair = orderItem?.kind === "repair";

            const works = (draft?.works || [])
                .map((w) => {
                    const title = String(w.title || "").trim();
                    const row = { title };
                    if (
                        isRepair &&
                        w.equipment_module_id != null &&
                        w.equipment_module_id !== ""
                    ) {
                        row.equipment_module_id = Number(w.equipment_module_id);
                    }
                    return row;
                })
                .filter((w) => {
                    if (isRepair) {
                        return Boolean(w.equipment_module_id);
                    }
                    return w.title !== "";
                });

            const payload = { works };
            if (orderItem?.kind === "sharpening") {
                const qty = this.clampCompletedQty(
                    orderItemId,
                    draft?.completed_qty,
                );
                payload.completed_qty = qty;
                const max = this.declaredQty(orderItemId);
                if (max != null) {
                    payload.max_qty = max;
                }
            }
            return payload;
        },
        async persistItem(orderItemId) {
            if (!this.canEditWorks || !this.job) {
                return;
            }
            const jobItem = this.jobItems.find((item) =>
                this.sameId(item.order_item_id, orderItemId),
            );
            if (!jobItem) {
                return;
            }

            const incomplete = this.incompleteWorks(orderItemId);

            this.saving = true;
            this.error = null;
            try {
                this.job = await workshopService.updateItem(
                    this.job.id,
                    this.draftKey(orderItemId),
                    this.buildPayload(jobItem),
                );
                this.syncDrafts();
                this.restoreIncompleteWorks(orderItemId, incomplete);
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось сохранить предмет";
                throw e;
            } finally {
                this.saving = false;
            }
        },
        async complete() {
            this.completing = true;
            this.error = null;
            try {
                for (const jobItem of this.jobItems) {
                    const incomplete = this.incompleteWorks(
                        jobItem.order_item_id,
                    );
                    if (incomplete.length > 0) {
                        this.error =
                            "Для ремонта укажите модуль у каждой работы";
                        return;
                    }
                    await this.persistItem(jobItem.order_item_id);
                }
                this.job = await workshopService.complete(this.job.id);
                this.order = await orderService.get(this.job.order_id);
                this.syncDrafts();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось завершить работы";
            } finally {
                this.completing = false;
            }
        },
        backToJobs() {
            this.$router.push({
                name: "pos.orders",
                query: { tab: "in_work" },
            });
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Заказ #{{ order?.id || "…" }}</h1>
            <button
                type="button"
                class="app-btn-ghost w-full sm:w-auto"
                @click="backToJobs"
            >
                К списку
            </button>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <template v-if="job && order && !loading">
            <div
                class="grid gap-4 lg:grid-cols-[minmax(16rem,20rem)_minmax(0,1fr)] lg:items-start lg:gap-6"
            >
                <aside class="space-y-3 lg:sticky lg:top-4">
                    <div
                        class="space-y-2 border border-slate-300 bg-white p-3 text-sm shadow-sm lg:p-4"
                    >
                        <p class="font-jost-medium text-dark-blue-500">
                            {{ statusLabel(order.status) }}
                        </p>
                        <dl class="space-y-1.5">
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Срочность</dt>
                                <dd class="text-right text-slate-800">
                                    {{
                                        URGENCY_LABELS[order.urgency] ||
                                        order.urgency
                                    }}
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
                                <dt class="text-slate-500">Задание</dt>
                                <dd class="text-right text-slate-800">
                                    {{ isOpen ? "В работе" : "Завершено" }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Позиций</dt>
                                <dd class="text-right text-slate-800">
                                    {{ jobItems.length }}
                                </dd>
                            </div>
                        </dl>
                        <p
                            v-if="saving"
                            class="border-t border-slate-100 pt-2 text-xs text-slate-500"
                        >
                            Сохраняю…
                        </p>
                    </div>

                    <section
                        class="space-y-2 border border-slate-300 bg-white p-3 text-sm shadow-sm lg:p-4"
                    >
                        <h2 class="text-sm font-jost-bold text-dark-blue-500">
                            Комментарии
                        </h2>
                        <ul
                            v-if="(order.comments || []).length"
                            ref="commentsList"
                            class="max-h-48 space-y-2 overflow-y-auto"
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
                        v-if="orderEquipments.length > 0"
                        class="space-y-3 border border-slate-300 bg-white p-3 text-sm shadow-sm lg:p-4"
                    >
                        <h2 class="text-sm font-jost-bold text-dark-blue-500">
                            Оборудование
                        </h2>
                        <div
                            v-for="entry in orderEquipments"
                            :key="entry.id"
                            class="space-y-2 border-t border-slate-100 pt-2 first:border-t-0 first:pt-0"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-jost-medium text-dark-blue-500">
                                    {{
                                        entry.equipment?.name || `№${entry.id}`
                                    }}
                                </p>
                                <span class="shrink-0 text-xs text-slate-500">
                                    #{{ entry.id }}
                                </span>
                            </div>
                            <dl class="space-y-1.5">
                                <div class="flex justify-between gap-2">
                                    <dt class="text-slate-500">Бренд</dt>
                                    <dd class="text-right text-slate-800">
                                        {{ entry.equipment?.brand || "—" }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt class="text-slate-500">Тип</dt>
                                    <dd class="text-right text-slate-800">
                                        {{ entry.equipment?.type || "—" }}
                                    </dd>
                                </div>
                            </dl>
                            <div>
                                <p
                                    class="text-xs font-jost-medium text-slate-500"
                                >
                                    Модули
                                </p>
                                <p
                                    v-if="
                                        !(entry.equipment?.modules || []).length
                                    "
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Модулей нет
                                </p>
                                <ul
                                    v-else
                                    class="mt-1 space-y-1 text-xs text-slate-700"
                                >
                                    <li
                                        v-for="module in entry.equipment
                                            .modules"
                                        :key="module.id || module.serial_number"
                                    >
                                        {{ module.name }}
                                        <span
                                            v-if="module.serial_number"
                                            class="text-slate-500"
                                        >
                                            · {{ module.serial_number }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>

                </aside>

                <div class="min-w-0 space-y-3">
                    <section
                        v-if="canEditWorks || canRequestApproval || isOnApproval || !isOpen"
                        class="space-y-1.5 border border-slate-300 bg-white px-2.5 py-2 shadow-sm"
                    >
                        <h2 class="text-xs font-jost-bold text-dark-blue-500">
                            Действия
                        </h2>
                        <template v-if="canEditWorks || canRequestApproval">
                            <textarea
                                v-if="canRequestApproval"
                                v-model="approvalBody"
                                rows="2"
                                class="app-field !py-1.5 !text-sm"
                                placeholder="Что согласовать с клиентом…"
                            />
                            <div class="flex flex-row flex-wrap gap-1.5">
                                <button
                                    v-if="canEditWorks"
                                    type="button"
                                    class="app-btn-primary !min-h-8 shrink-0 !px-2.5 !py-1.5 !text-sm"
                                    :disabled="completing || saving"
                                    @click="complete"
                                >
                                    {{
                                        completing
                                            ? "Завершаю…"
                                            : "Работы выполнены"
                                    }}
                                </button>
                                <button
                                    v-if="canRequestApproval"
                                    type="button"
                                    class="app-btn-secondary !min-h-8 shrink-0 !px-2.5 !py-1.5 !text-sm"
                                    :disabled="requestingApproval"
                                    @click="submitApproval"
                                >
                                    {{
                                        requestingApproval
                                            ? "Отправка…"
                                            : "На согласование"
                                    }}
                                </button>
                            </div>
                        </template>
                        <p
                            v-else-if="isOnApproval"
                            class="text-xs text-amber-800"
                        >
                            Ждём решение менеджера по согласованию
                        </p>
                        <p
                            v-else
                            class="text-xs text-slate-700"
                        >
                            Работы по этому заказу завершены.
                        </p>
                    </section>

                    <h2
                        class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                    >
                        Производство
                    </h2>

                    <div
                        v-for="jobItem in jobItems"
                        :key="jobItem.order_item_id"
                        class="space-y-3 border border-slate-300 bg-white p-3 shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
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
                                </div>

                                <template
                                    v-if="
                                        orderItem(jobItem.order_item_id)
                                            ?.kind === 'sharpening'
                                    "
                                >
                                    <p class="text-sm text-slate-700">
                                        {{
                                            orderItem(jobItem.order_item_id)
                                                ?.title || "—"
                                        }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Заявлено:
                                        {{
                                            orderItem(jobItem.order_item_id)
                                                ?.quantity ?? "—"
                                        }}
                                    </p>
                                </template>

                                <template
                                    v-else-if="
                                        orderItem(jobItem.order_item_id)
                                            ?.kind === 'repair'
                                    "
                                >
                                    <p class="text-sm text-slate-700">
                                        Оборудование:
                                        {{
                                            orderItem(jobItem.order_item_id)
                                                ?.equipment_id
                                                ? `#${orderItem(jobItem.order_item_id).equipment_id}`
                                                : "—"
                                        }}
                                    </p>
                                    <p
                                        v-if="
                                            orderItem(jobItem.order_item_id)
                                                ?.problem
                                        "
                                        class="text-xs text-slate-500"
                                    >
                                        {{
                                            orderItem(jobItem.order_item_id)
                                                .problem
                                        }}
                                    </p>
                                    <p
                                        v-if="
                                            modulesFor(jobItem.order_item_id)
                                                .length === 0
                                        "
                                        class="text-xs text-red-600"
                                    >
                                        У оборудования нет модулей — попросите
                                        менеджера добавить.
                                    </p>
                                </template>
                            </div>

                            <label
                                v-if="
                                    orderItem(jobItem.order_item_id)?.kind ===
                                    'sharpening'
                                "
                                class="block w-full shrink-0 space-y-1 sm:w-36"
                            >
                                <span class="text-xs text-slate-500">
                                    Заточено
                                    <template
                                        v-if="
                                            orderItem(jobItem.order_item_id)
                                                ?.quantity != null
                                        "
                                    >
                                        (0…{{
                                            orderItem(jobItem.order_item_id)
                                                .quantity
                                        }})
                                    </template>
                                </span>
                                <input
                                    v-model="
                                        drafts[draftKey(jobItem.order_item_id)]
                                            .completed_qty
                                    "
                                    type="number"
                                    min="0"
                                    :max="
                                        orderItem(jobItem.order_item_id)
                                            ?.quantity ?? undefined
                                    "
                                    class="app-field"
                                    :disabled="!canEditWorks"
                                    @blur="onQtyBlur(jobItem.order_item_id)"
                                />
                            </label>
                        </div>

                        <div class="space-y-2 border-t border-slate-100 pt-2">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-jost-medium text-slate-700"
                                >
                                    Работы
                                </span>
                                <button
                                    v-if="canEditWorks"
                                    type="button"
                                    class="text-sm text-pink-700 hover:underline"
                                    @click="addWork(jobItem.order_item_id)"
                                >
                                    + работа
                                </button>
                            </div>
                            <div
                                v-for="(work, index) in drafts[
                                    draftKey(jobItem.order_item_id)
                                ].works"
                                :key="index"
                                class="space-y-1.5"
                            >
                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <select
                                        v-if="isRepair(jobItem.order_item_id)"
                                        v-model="work.equipment_module_id"
                                        class="app-field sm:w-48"
                                        :disabled="!canEditWorks"
                                        @change="
                                            onWorkBlur(jobItem.order_item_id)
                                        "
                                    >
                                        <option value="">Модуль…</option>
                                        <option
                                            v-for="module in modulesFor(
                                                jobItem.order_item_id,
                                            )"
                                            :key="module.id"
                                            :value="Number(module.id)"
                                        >
                                            {{ module.name }}
                                            ({{ module.serial_number }})
                                        </option>
                                    </select>
                                    <input
                                        v-model="work.title"
                                        type="text"
                                        placeholder="Что сделано"
                                        class="app-field flex-1"
                                        :disabled="!canEditWorks"
                                        @blur="
                                            onWorkBlur(jobItem.order_item_id)
                                        "
                                    />
                                    <button
                                        v-if="canEditWorks"
                                        type="button"
                                        class="shrink-0 text-sm text-red-600 hover:underline"
                                        @click="
                                            removeWork(
                                                jobItem.order_item_id,
                                                index,
                                            )
                                        "
                                    >
                                        Убрать
                                    </button>
                                </div>
                                <div
                                    v-if="
                                        canEditWorks &&
                                        !isRepair(jobItem.order_item_id)
                                    "
                                    class="flex flex-wrap gap-1"
                                >
                                    <button
                                        v-for="preset in workTitlePresets"
                                        :key="preset"
                                        type="button"
                                        class="border-0 bg-transparent px-1 py-0.5 text-[11px] leading-tight text-slate-500 underline decoration-slate-300 underline-offset-2 hover:text-slate-800 hover:decoration-slate-500"
                                        @click="
                                            applyWorkTitle(
                                                jobItem.order_item_id,
                                                index,
                                                preset,
                                            )
                                        "
                                    >
                                        {{ preset }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
