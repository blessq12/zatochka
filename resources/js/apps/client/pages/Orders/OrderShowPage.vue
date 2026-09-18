<script>
import {
    BILLING_LABELS,
    KIND_LABELS,
    orderService,
    statusLabel,
    URGENCY_LABELS,
} from "../../services/OrderService.js";

export default {
    name: "ClientOrderShowPage",
    data() {
        return {
            order: null,
            loading: false,
            savingReview: false,
            error: null,
            reviewError: null,
            reviewForm: { rating: 5, text: "" },
            statusLabel,
            BILLING_LABELS,
            URGENCY_LABELS,
            KIND_LABELS,
        };
    },
    computed: {
        canReview() {
            return (
                this.order?.status === "issued" && this.order?.review == null
            );
        },
    },
    mounted() {
        this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.order = await orderService.get(this.$route.params.id);
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить заказ";
            } finally {
                this.loading = false;
            }
        },
        async submitReview() {
            this.savingReview = true;
            this.reviewError = null;
            try {
                this.order = await orderService.review(this.order.id, {
                    rating: Number(this.reviewForm.rating),
                    text: this.reviewForm.text || null,
                });
            } catch (e) {
                this.reviewError =
                    e.response?.data?.message || "Не удалось отправить отзыв";
            } finally {
                this.savingReview = false;
            }
        },
        back() {
            this.$router.push({ name: "client.orders" });
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Заказ #{{ $route.params.id }}</h1>
            <button type="button" class="app-btn-ghost w-full sm:w-auto" @click="back">
                К списку
            </button>
        </div>

        <p v-if="loading" class="text-base text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-base text-red-600">{{ error }}</p>

        <template v-if="order && !loading">
            <section class="app-panel">
                <p class="font-jost-medium text-dark-blue-500">
                    {{ statusLabel(order.status) }}
                </p>
                <dl class="mt-3 space-y-2 text-base">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Оплата</dt>
                        <dd>
                            {{
                                BILLING_LABELS[order.billing_type] ||
                                order.billing_type
                            }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Срочность</dt>
                        <dd>
                            {{ URGENCY_LABELS[order.urgency] || order.urgency }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Ориентир</dt>
                        <dd>{{ order.estimated_cost }} ₽</dd>
                    </div>
                    <div v-if="order.needs_delivery" class="flex justify-between gap-2">
                        <dt class="text-slate-500">Доставка</dt>
                        <dd class="text-right">
                            {{ order.delivery_address || "—" }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="app-panel">
                <h2 class="text-base font-jost-bold text-dark-blue-500">Состав</h2>
                <ul class="mt-3 space-y-2">
                    <li
                        v-for="item in order.items"
                        :key="item.id || `${item.kind}-${item.title}`"
                        class="border border-slate-200 p-3 text-base"
                    >
                        <p class="font-jost-medium">
                            {{ KIND_LABELS[item.kind] || item.kind }}
                        </p>
                        <p v-if="item.kind === 'sharpening'" class="text-slate-600">
                            {{ item.title }} × {{ item.quantity }}
                        </p>
                        <p v-else class="text-slate-600">
                            Оборудование #{{ item.equipment_id }}
                            <span v-if="item.problem"> — {{ item.problem }}</span>
                        </p>
                    </li>
                </ul>
            </section>

            <section v-if="order.review" class="app-panel">
                <h2 class="text-base font-jost-bold text-dark-blue-500">Отзыв</h2>
                <p class="mt-2 text-base">
                    Оценка: {{ order.review.rating }}/5
                </p>
                <p v-if="order.review.text" class="mt-1 text-slate-600">
                    {{ order.review.text }}
                </p>
            </section>

            <section v-else-if="canReview" class="app-panel space-y-3">
                <h2 class="text-base font-jost-bold text-dark-blue-500">
                    Оставить отзыв
                </h2>
                <p v-if="reviewError" class="text-base text-red-600">
                    {{ reviewError }}
                </p>
                <label class="block text-base">
                    Оценка
                    <select v-model.number="reviewForm.rating" class="app-field mt-1">
                        <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                    </select>
                </label>
                <label class="block text-base">
                    Комментарий
                    <textarea
                        v-model="reviewForm.text"
                        rows="3"
                        class="app-field mt-1"
                        placeholder="По желанию"
                    />
                </label>
                <button
                    type="button"
                    class="app-btn-primary"
                    :disabled="savingReview"
                    @click="submitReview"
                >
                    Отправить
                </button>
            </section>
        </template>
    </div>
</template>
