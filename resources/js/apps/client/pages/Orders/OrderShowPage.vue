<script>
import ClientSectionCard from "../../components/Layout/ClientSectionCard.vue";
import {
    BILLING_LABELS,
    KIND_LABELS,
    orderService,
    statusLabel,
    URGENCY_LABELS,
} from "../../services/OrderService.js";

export default {
    name: "ClientOrderShowPage",
    components: { ClientSectionCard },
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
            fieldClass:
                "w-full border border-white/20 bg-white/60 px-4 py-3 text-dark-gray-500 backdrop-blur-md outline-none focus:border-[#C3006B] dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200",
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
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2
                class="text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 sm:text-2xl"
            >
                Заказ #{{ $route.params.id }}
            </h2>
            <button
                type="button"
                class="w-full border border-dark-blue-500/30 px-4 py-3 font-jost-medium text-dark-gray-500 transition hover:bg-white/60 sm:w-auto dark:text-gray-200"
                @click="back"
            >
                К списку
            </button>
        </div>

        <p v-if="loading" class="text-base text-dark-gray-500">Загрузка…</p>
        <p v-if="error" class="text-base text-red-600">{{ error }}</p>

        <template v-if="order && !loading">
            <ClientSectionCard title="ПАРАМЕТРЫ">
                <p class="font-jost-medium text-dark-blue-500 dark:text-dark-blue-300">
                    {{ statusLabel(order.status) }}
                </p>
                <dl class="mt-4 space-y-3 text-base">
                    <div class="flex justify-between gap-2">
                        <dt class="text-dark-gray-500 dark:text-gray-400">Оплата</dt>
                        <dd class="text-dark-gray-500 dark:text-gray-200">
                            {{
                                BILLING_LABELS[order.billing_type] ||
                                order.billing_type
                            }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-dark-gray-500 dark:text-gray-400">
                            Срочность
                        </dt>
                        <dd class="text-dark-gray-500 dark:text-gray-200">
                            {{ URGENCY_LABELS[order.urgency] || order.urgency }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-dark-gray-500 dark:text-gray-400">
                            Ориентир
                        </dt>
                        <dd class="text-dark-gray-500 dark:text-gray-200">
                            {{ order.estimated_cost }} ₽
                        </dd>
                    </div>
                    <div
                        v-if="order.needs_delivery"
                        class="flex justify-between gap-2"
                    >
                        <dt class="text-dark-gray-500 dark:text-gray-400">
                            Доставка
                        </dt>
                        <dd class="text-right text-dark-gray-500 dark:text-gray-200">
                            {{ order.delivery_address || "—" }}
                        </dd>
                    </div>
                </dl>
            </ClientSectionCard>

            <ClientSectionCard title="СОСТАВ">
                <ul class="space-y-3">
                    <li
                        v-for="item in order.items"
                        :key="item.id || `${item.kind}-${item.title}`"
                        class="border border-white/20 bg-white/60 p-4 backdrop-blur-md dark:border-gray-700/20 dark:bg-gray-800/60"
                    >
                        <p class="font-jost-medium text-dark-blue-500 dark:text-dark-blue-300">
                            {{ KIND_LABELS[item.kind] || item.kind }}
                        </p>
                        <p
                            v-if="item.kind === 'sharpening'"
                            class="mt-1 text-dark-gray-500 dark:text-gray-300"
                        >
                            {{ item.title }} × {{ item.quantity }}
                        </p>
                        <p v-else class="mt-1 text-dark-gray-500 dark:text-gray-300">
                            Оборудование #{{ item.equipment_id }}
                            <span v-if="item.problem"> — {{ item.problem }}</span>
                        </p>
                    </li>
                </ul>
            </ClientSectionCard>

            <ClientSectionCard v-if="order.review" title="ОТЗЫВ">
                <p class="text-base text-dark-gray-500 dark:text-gray-200">
                    Оценка: {{ order.review.rating }}/5
                </p>
                <p
                    v-if="order.review.text"
                    class="mt-2 text-dark-gray-500 dark:text-gray-300"
                >
                    {{ order.review.text }}
                </p>
            </ClientSectionCard>

            <ClientSectionCard v-else-if="canReview" title="ОСТАВИТЬ ОТЗЫВ">
                <p v-if="reviewError" class="mb-4 text-base text-red-600">
                    {{ reviewError }}
                </p>
                <label class="mb-4 block">
                    <span
                        class="mb-2 block text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200"
                    >
                        Оценка
                    </span>
                    <select v-model.number="reviewForm.rating" :class="fieldClass">
                        <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                    </select>
                </label>
                <label class="mb-6 block">
                    <span
                        class="mb-2 block text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200"
                    >
                        Комментарий
                    </span>
                    <textarea
                        v-model="reviewForm.text"
                        rows="3"
                        :class="fieldClass"
                        placeholder="По желанию"
                    />
                </label>
                <button
                    type="button"
                    class="bg-[#C3006B] px-8 py-3 font-jost-bold text-white hover:bg-[#A8005A] disabled:opacity-50"
                    :disabled="savingReview"
                    @click="submitReview"
                >
                    Отправить
                </button>
            </ClientSectionCard>
        </template>
    </div>
</template>
