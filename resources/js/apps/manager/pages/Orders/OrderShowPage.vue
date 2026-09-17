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
            masters: [],
            equipments: [],
            masterId: "",
            editItems: [],
            loading: false,
            saving: false,
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
        nextStatuses() {
            return allowedTransitions(this.order?.status);
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
        async loadEquipments(clientId) {
            try {
                this.equipments = await equipmentService.list(clientId);
            } catch {
                this.equipments = [];
            }
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
                await this.loadEquipments(this.order.client_id);
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
        async transition(status) {
            this.saving = true;
            this.error = null;
            try {
                this.order = await orderService.transition(this.order.id, status);
                this.syncEditItems();
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
    <div class="mx-auto max-w-2xl space-y-6">
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
            <div class="space-y-2 border border-slate-200 bg-white p-4 text-sm">
                <p>
                    <span class="text-slate-500">Статус:</span>
                    {{ statusLabel(order.status) }}
                </p>
                <p>
                    <span class="text-slate-500">Клиент id:</span>
                    {{ order.client_id }}
                </p>
                <p>
                    <span class="text-slate-500">Мастер:</span>
                    {{ masterName(order.master_id) }}
                </p>
                <p>
                    <span class="text-slate-500">Оплата:</span>
                    {{ BILLING_LABELS[order.billing_type] || order.billing_type }}
                </p>
                <p>
                    <span class="text-slate-500">Ориентировочная стоимость:</span>
                    {{ order.estimated_cost }}
                </p>
                <p>
                    <span class="text-slate-500">Доставка:</span>
                    <template v-if="order.needs_delivery">
                        да — {{ order.delivery_address || "—" }}
                    </template>
                    <template v-else>нет</template>
                </p>
                <p>
                    <span class="text-slate-500">Срочность:</span>
                    {{ URGENCY_LABELS[order.urgency] || order.urgency }}
                </p>
                <p v-if="order.review">
                    <span class="text-slate-500">Отзыв:</span>
                    {{ order.review.rating }}/5
                    <span v-if="order.review.text">— {{ order.review.text }}</span>
                </p>
            </div>

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

            <section v-if="nextStatuses.length" class="space-y-3">
                <h2 class="text-lg font-jost-bold text-dark-blue-500">
                    Смена статуса
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

            <section class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-jost-bold text-dark-blue-500">
                        Предметы
                    </h2>
                    <div v-if="canEditItems" class="flex gap-3">
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

                <template v-if="canEditItems">
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
                </template>

                <ul v-else class="divide-y divide-slate-100 border border-slate-200">
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
        </template>
    </div>
</template>
