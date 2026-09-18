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
                "w-full border border-white/20 bg-white/60 px-4 py-3.5 text-dark-gray-500 shadow-lg outline-none backdrop-blur-md transition-all duration-300 focus:border-[#C20A6C]/50 focus:ring-2 focus:ring-[#C20A6C]/30 dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200 sm:px-6 sm:py-4",
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
    <div class="space-y-5 lg:space-y-6">
        <div class="flex items-center justify-end lg:justify-between">
            <h2
                class="hidden font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 lg:block lg:text-2xl"
            >
                Заказ #{{ $route.params.id }}
            </h2>
            <button
                type="button"
                class="text-base font-jost-medium text-dark-gray-500 hover:text-[#C20A6C] dark:text-gray-200 lg:border lg:border-dark-blue-500/30 lg:px-4 lg:py-3 lg:hover:bg-white/60"
                @click="back"
            >
                ← К списку
            </button>
        </div>

        <p v-if="loading" class="text-base text-dark-gray-500 lg:text-base">
            Загрузка…
        </p>
        <p v-if="error" class="text-base text-red-600 lg:text-base">{{ error }}</p>

        <template v-if="order && !loading">
            <ClientSectionCard title="ПАРАМЕТРЫ">
                <p
                    class="text-base font-jost-medium text-dark-blue-500 dark:text-dark-blue-300 lg:text-base"
                >
                    {{ statusLabel(order.status) }}
                </p>
                <dl class="mt-2 space-y-2 text-base lg:mt-4 lg:space-y-3 lg:text-base">
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
                <ul
                    class="divide-y divide-dark-blue-500/10 dark:divide-white/10 lg:space-y-3 lg:divide-y-0"
                >
                    <li
                        v-for="item in order.items"
                        :key="item.id || `${item.kind}-${item.title}`"
                        class="py-3.5 first:pt-0 last:pb-0 lg:border lg:border-white/20 lg:bg-white/60 lg:p-4 lg:backdrop-blur-md dark:lg:border-gray-700/20 dark:lg:bg-gray-800/60"
                    >
                        <p
                            class="text-base font-jost-medium text-dark-blue-500 dark:text-dark-blue-300 lg:text-base"
                        >
                            {{ KIND_LABELS[item.kind] || item.kind }}
                        </p>
                        <p
                            v-if="item.kind === 'sharpening'"
                            class="mt-1 text-base text-dark-gray-500 dark:text-gray-300"
                        >
                            {{ item.title }} × {{ item.quantity }}
                        </p>
                        <p
                            v-else
                            class="mt-1 text-base text-dark-gray-500 dark:text-gray-300"
                        >
                            Оборудование #{{ item.equipment_id }}
                            <span v-if="item.problem"> — {{ item.problem }}</span>
                        </p>
                    </li>
                </ul>
            </ClientSectionCard>

            <ClientSectionCard v-if="order.review" title="ОТЗЫВ">
                <p class="text-base text-dark-gray-500 dark:text-gray-200 lg:text-base">
                    Оценка: {{ order.review.rating }}/5
                </p>
                <p
                    v-if="order.review.text"
                    class="mt-1 text-base text-dark-gray-500 dark:text-gray-300 lg:mt-2"
                >
                    {{ order.review.text }}
                </p>
            </ClientSectionCard>

            <ClientSectionCard v-else-if="canReview" title="ОСТАВИТЬ ОТЗЫВ">
                <p v-if="reviewError" class="mb-2 text-base text-red-600 lg:mb-4 lg:text-base">
                    {{ reviewError }}
                </p>
                <label class="mb-4 block lg:mb-4">
                    <span
                        class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200"
                    >
                        Оценка
                    </span>
                    <select v-model.number="reviewForm.rating" :class="fieldClass">
                        <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                    </select>
                </label>
                <label class="mb-4 block lg:mb-6">
                    <span
                        class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200"
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
                    class="w-full bg-[#C20A6C] px-6 py-3.5 font-jost-bold text-white hover:bg-[#a0085a] disabled:opacity-50 lg:w-auto lg:px-8 lg:py-3"
                    :disabled="savingReview"
                    @click="submitReview"
                >
                    Отправить
                </button>
            </ClientSectionCard>
        </template>
    </div>
</template>
