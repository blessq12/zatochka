<script>
import {
    KIND_LABELS,
    URGENCY_LABELS,
    orderService,
    statusLabel,
} from "../../services/OrderService.js";
import { workshopService } from "../../services/WorkshopService.js";

function emptyWork() {
    return { title: "" };
}

export default {
    name: "JobPage",
    data() {
        return {
            job: null,
            order: null,
            drafts: {},
            loading: false,
            saving: false,
            completing: false,
            error: null,
            KIND_LABELS,
            URGENCY_LABELS,
            statusLabel,
        };
    },
    computed: {
        isOpen() {
            return this.job?.status === "open";
        },
        jobItems() {
            return this.job?.items || [];
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
                              }))
                            : [emptyWork()],
                };
            }
            this.drafts = drafts;
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.job = await workshopService.get(this.$route.params.id);
                this.order = await orderService.get(this.job.order_id);
                this.syncDrafts();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить задание";
                this.job = null;
                this.order = null;
            } finally {
                this.loading = false;
            }
        },
        addWork(orderItemId) {
            this.drafts[this.draftKey(orderItemId)].works.push(emptyWork());
        },
        removeWork(orderItemId, index) {
            const works = this.drafts[this.draftKey(orderItemId)].works;
            works.splice(index, 1);
            if (works.length === 0) {
                works.push(emptyWork());
            }
            if (this.isOpen) {
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
            const works = (draft?.works || [])
                .map((w) => ({ title: String(w.title || "").trim() }))
                .filter((w) => w.title !== "");

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
            if (!this.isOpen || !this.job) {
                return;
            }
            const jobItem = this.jobItems.find((item) =>
                this.sameId(item.order_item_id, orderItemId),
            );
            if (!jobItem) {
                return;
            }

            this.saving = true;
            this.error = null;
            try {
                this.job = await workshopService.updateItem(
                    this.job.id,
                    this.draftKey(orderItemId),
                    this.buildPayload(jobItem),
                );
                this.syncDrafts();
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
                    await this.persistItem(jobItem.order_item_id);
                }
                this.job = await workshopService.complete(this.job.id);
                this.order = await orderService.get(this.job.order_id);
                this.syncDrafts();
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    "Не удалось завершить работы";
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
            <h1 class="app-page-title">
                Заказ #{{ order?.id || "…" }}
            </h1>
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
                                <dt class="text-slate-500">Задание</dt>
                                <dd class="text-right text-slate-800">
                                    {{
                                        isOpen ? "В работе" : "Завершено"
                                    }}
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
                        v-if="isOpen"
                        class="space-y-2 border border-slate-300 bg-white p-3 shadow-sm lg:p-4"
                    >
                        <h2 class="text-sm font-jost-bold text-dark-blue-500">
                            Действие
                        </h2>
                        <button
                            type="button"
                            class="app-btn-primary w-full"
                            :disabled="completing || saving"
                            @click="complete"
                        >
                            {{
                                completing
                                    ? "Завершаю…"
                                    : "Работы выполнены"
                            }}
                        </button>
                    </section>

                    <p
                        v-else
                        class="border border-slate-300 bg-slate-50 px-3 py-3 text-sm text-slate-700 shadow-sm lg:px-4"
                    >
                        Работы по этому заказу завершены.
                    </p>
                </aside>

                <div class="min-w-0 space-y-3">
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
                                        drafts[
                                            draftKey(jobItem.order_item_id)
                                        ].completed_qty
                                    "
                                    type="number"
                                    min="0"
                                    :max="
                                        orderItem(jobItem.order_item_id)
                                            ?.quantity ?? undefined
                                    "
                                    class="app-field"
                                    :disabled="!isOpen"
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
                                    v-if="isOpen"
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
                                class="flex gap-2"
                            >
                                <input
                                    v-model="work.title"
                                    type="text"
                                    placeholder="Что сделано"
                                    class="app-field flex-1"
                                    :disabled="!isOpen"
                                    @blur="onWorkBlur(jobItem.order_item_id)"
                                />
                                <button
                                    v-if="isOpen"
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
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
