<script>
import { formatOrderDate } from "../../../../shared/formatOrderDate.js";
import {
    draftSourceLabel,
    draftStatusLabel,
    orderDraftService,
} from "../../services/OrderDraftService.js";

export default {
    name: "OrderDraftListPage",
    data() {
        return {
            items: [],
            status: "pending",
            source: "",
            phone: "",
            loading: false,
            error: null,
            draftStatusLabel,
            draftSourceLabel,
            formatOrderDate,
        };
    },
    mounted() {
        this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.items = await orderDraftService.list({
                    status: this.status || null,
                    source: this.source || null,
                    phone: this.phone || null,
                });
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить заявки";
                this.items = [];
            } finally {
                this.loading = false;
            }
        },
        open(draft) {
            this.$router.push({
                name: "manager.order-drafts.show",
                params: { id: draft.id },
            });
        },
        summary(draft) {
            const items = draft.payload?.items || [];
            if (items.length > 0) {
                return items
                    .map((item) =>
                        item.kind === "repair"
                            ? `Ремонт #${item.equipment_id || "—"}`
                            : `${item.title || "Заточка"} × ${item.quantity || 1}`,
                    )
                    .join(", ");
            }
            const intake = draft.payload?.intake_data || {};
            return (
                intake.device_name ||
                intake.tool_type ||
                intake.equipment_type ||
                draft.service_type ||
                "—"
            );
        },
    },
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-wrap items-end gap-3">
            <label class="block">
                <span class="mb-1 block text-sm text-gray-600">Статус</span>
                <select
                    v-model="status"
                    class="border px-3 py-2"
                    @change="load"
                >
                    <option value="pending">Ожидает</option>
                    <option value="promoted">В заказ</option>
                    <option value="cancelled">Отменён</option>
                    <option value="">Все</option>
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-sm text-gray-600">Источник</span>
                <select
                    v-model="source"
                    class="border px-3 py-2"
                    @change="load"
                >
                    <option value="">Все</option>
                    <option value="public">Сайт</option>
                    <option value="client_lk">ЛК</option>
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-sm text-gray-600">Телефон</span>
                <input
                    v-model="phone"
                    type="text"
                    class="border px-3 py-2"
                    @keyup.enter="load"
                />
            </label>
            <button
                type="button"
                class="bg-[#C20A6C] px-4 py-2 text-white"
                @click="load"
            >
                Найти
            </button>
        </div>

        <p v-if="loading">Загрузка…</p>
        <p v-else-if="error" class="text-red-600">{{ error }}</p>
        <p v-else-if="items.length === 0">Заявок нет</p>

        <div v-else class="divide-y border">
            <button
                v-for="draft in items"
                :key="draft.id"
                type="button"
                class="flex w-full items-start justify-between gap-3 px-4 py-3 text-left hover:bg-gray-50"
                @click="open(draft)"
            >
                <div>
                    <p class="font-semibold">
                        #{{ draft.id }} ·
                        {{ draftSourceLabel(draft.source) }}
                    </p>
                    <p class="text-sm text-gray-600">
                        {{ draft.full_name || "Клиент #" + draft.client_id }}
                        · {{ draft.phone || "—" }}
                    </p>
                    <p class="mt-1 text-sm">{{ summary(draft) }}</p>
                </div>
                <div class="text-right text-sm">
                    <p>{{ draftStatusLabel(draft.status) }}</p>
                    <p class="text-gray-500">
                        {{ formatOrderDate(draft.created_at) }}
                    </p>
                </div>
            </button>
        </div>
    </div>
</template>
