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
    <div class="w-full space-y-4 sm:space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-jost-bold text-dark-blue-500">
                Заказ #{{ order?.id || "…" }}
            </h1>
            <button
                type="button"
                class="border border-slate-300 px-4 py-2 text-sm text-slate-600"
                @click="backToJobs"
            >
                К списку
            </button>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <template v-if="job && order && !loading">
            <section class="space-y-1 border border-slate-200 bg-white p-4 text-sm">
                <p>
                    <span class="text-slate-500">Статус:</span>
                    {{ statusLabel(order.status) }}
                </p>
                <p>
                    <span class="text-slate-500">Срочность:</span>
                    {{ URGENCY_LABELS[order.urgency] || order.urgency }}
                </p>
                <p v-if="saving" class="text-xs text-slate-400">Сохраняю…</p>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-jost-bold text-dark-blue-500">
                    Производство
                </h2>

                <div
                    v-for="jobItem in jobItems"
                    :key="jobItem.order_item_id"
                    class="space-y-3 border border-slate-200 bg-white p-4"
                >
                    <div class="space-y-1">
                        <div class="text-sm font-jost-medium text-dark-blue-500">
                            {{
                                KIND_LABELS[orderItem(jobItem.order_item_id)?.kind] ||
                                "Позиция"
                            }}
                        </div>

                        <template
                            v-if="orderItem(jobItem.order_item_id)?.kind === 'sharpening'"
                        >
                            <p class="text-sm text-slate-600">
                                {{ orderItem(jobItem.order_item_id)?.title || "—" }}
                            </p>
                            <p class="text-sm text-slate-600">
                                Заявлено:
                                {{ orderItem(jobItem.order_item_id)?.quantity ?? "—" }}
                            </p>
                        </template>

                        <template
                            v-else-if="orderItem(jobItem.order_item_id)?.kind === 'repair'"
                        >
                            <p class="text-sm text-slate-600">
                                Оборудование:
                                {{
                                    orderItem(jobItem.order_item_id)?.equipment_id
                                        ? `#${orderItem(jobItem.order_item_id).equipment_id}`
                                        : "—"
                                }}
                            </p>
                            <p
                                v-if="orderItem(jobItem.order_item_id)?.problem"
                                class="text-sm text-slate-600"
                            >
                                Проблема:
                                {{ orderItem(jobItem.order_item_id).problem }}
                            </p>
                        </template>
                    </div>

                    <label
                        v-if="orderItem(jobItem.order_item_id)?.kind === 'sharpening'"
                        class="block space-y-1"
                    >
                        <span class="text-sm text-slate-600">
                            Заточено
                            <span
                                v-if="orderItem(jobItem.order_item_id)?.quantity != null"
                            >
                                (0…{{ orderItem(jobItem.order_item_id).quantity }})
                            </span>
                        </span>
                        <input
                            v-model="drafts[draftKey(jobItem.order_item_id)].completed_qty"
                            type="number"
                            min="0"
                            :max="orderItem(jobItem.order_item_id)?.quantity ?? undefined"
                            class="w-full border border-slate-300 px-3 py-2"
                            :disabled="!isOpen"
                            @blur="onQtyBlur(jobItem.order_item_id)"
                        />
                    </label>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-jost-medium text-slate-700">
                                Работы
                            </span>
                            <button
                                v-if="isOpen"
                                type="button"
                                class="text-sm text-pink-600 hover:underline"
                                @click="addWork(jobItem.order_item_id)"
                            >
                                + работа
                            </button>
                        </div>
                        <div
                            v-for="(work, index) in drafts[draftKey(jobItem.order_item_id)]
                                .works"
                            :key="index"
                            class="flex gap-2"
                        >
                            <input
                                v-model="work.title"
                                type="text"
                                placeholder="Что сделано"
                                class="flex-1 border border-slate-300 px-3 py-2"
                                :disabled="!isOpen"
                                @blur="onWorkBlur(jobItem.order_item_id)"
                            />
                            <button
                                v-if="isOpen"
                                type="button"
                                class="text-sm text-red-600 hover:underline"
                                @click="removeWork(jobItem.order_item_id, index)"
                            >
                                Убрать
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <button
                v-if="isOpen"
                type="button"
                class="w-full bg-pink-500 px-4 py-3 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                :disabled="completing || saving"
                @click="complete"
            >
                {{ completing ? "Завершаю…" : "Работы выполнены" }}
            </button>

            <p
                v-else
                class="border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600"
            >
                Работы по этому заказу завершены.
            </p>
        </template>
    </div>
</template>
