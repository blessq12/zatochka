<script>
import { actorService } from "../../services/ActorService.js";
import { equipmentService } from "../../services/EquipmentService.js";
import { KIND_LABELS, URGENCY_LABELS } from "../../services/OrderService.js";
import {
    draftSourceLabel,
    draftStatusLabel,
    orderDraftService,
} from "../../services/OrderDraftService.js";

function emptySharpening() {
    return { kind: "sharpening", title: "", quantity: 1 };
}

export default {
    name: "OrderDraftPromotePage",
    data() {
        return {
            draft: null,
            equipments: [],
            selectedClient: null,
            saving: false,
            cancelling: false,
            error: null,
            KIND_LABELS,
            URGENCY_LABELS,
            draftStatusLabel,
            draftSourceLabel,
            createClient: true,
            form: {
                client_id: "",
                billing_type: "paid",
                urgency: "normal",
                estimated_cost: "",
                needs_delivery: false,
                delivery_address: "",
                items: [emptySharpening()],
            },
        };
    },
    computed: {
        draftId() {
            return Number(this.$route.params.id);
        },
        canPromote() {
            return this.draft?.status === "pending";
        },
        needsCreateClient() {
            return this.draft?.source === "public" && !this.draft?.client_id;
        },
    },
    async mounted() {
        await this.load();
    },
    methods: {
        async load() {
            this.error = null;
            try {
                this.draft = await orderDraftService.get(this.draftId);
                this.form.needs_delivery = Boolean(this.draft.needs_delivery);
                this.form.delivery_address =
                    this.draft.delivery_address || "";
                this.createClient = this.needsCreateClient;

                if (this.draft.client_id) {
                    this.form.client_id = String(this.draft.client_id);
                    try {
                        this.selectedClient = await actorService.get(
                            "clients",
                            this.draft.client_id,
                        );
                        this.equipments = await equipmentService.list(
                            this.draft.client_id,
                        );
                    } catch {
                        this.selectedClient = null;
                    }
                }

                const items = this.draft.payload?.items || [];
                if (items.length > 0) {
                    this.form.items = items.map((item) =>
                        item.kind === "repair"
                            ? {
                                  kind: "repair",
                                  equipment_id: String(item.equipment_id || ""),
                                  problem: item.problem || "",
                              }
                            : {
                                  kind: "sharpening",
                                  title: item.title || "",
                                  quantity: item.quantity || 1,
                              },
                    );
                } else if (this.draft.service_type === "sharpening") {
                    const intake = this.draft.payload?.intake_data || {};
                    this.form.items = [
                        {
                            kind: "sharpening",
                            title: intake.tool_type || "Заточка",
                            quantity: intake.tools_count || 1,
                        },
                    ];
                }
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Заявка не найдена";
                this.draft = null;
            }
        },
        addSharpening() {
            this.form.items.push(emptySharpening());
        },
        addRepair() {
            this.form.items.push({
                kind: "repair",
                equipment_id: "",
                problem: "",
            });
        },
        removeItem(index) {
            this.form.items.splice(index, 1);
        },
        async promote() {
            if (!this.canPromote) return;
            this.saving = true;
            this.error = null;
            try {
                if (!this.form.estimated_cost && this.form.estimated_cost !== 0) {
                    throw new Error("Укажите ориентировочную стоимость");
                }
                if (this.form.items.length === 0) {
                    throw new Error("Добавьте хотя бы одну позицию");
                }
                if (
                    this.form.needs_delivery &&
                    !this.form.delivery_address?.trim()
                ) {
                    throw new Error("Укажите адрес доставки");
                }
                if (
                    !this.needsCreateClient &&
                    !this.form.client_id &&
                    !this.createClient
                ) {
                    throw new Error("Выберите клиента");
                }

                const items = this.form.items.map((item) => {
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

                const payload = {
                    billing_type: this.form.billing_type,
                    urgency: this.form.urgency,
                    estimated_cost: String(this.form.estimated_cost),
                    needs_delivery: this.form.needs_delivery,
                    delivery_address: this.form.needs_delivery
                        ? this.form.delivery_address
                        : null,
                    items,
                };

                if (this.needsCreateClient && this.createClient) {
                    payload.create_client = true;
                } else if (this.form.client_id) {
                    payload.client_id = Number(this.form.client_id);
                }

                const result = await orderDraftService.promote(
                    this.draftId,
                    payload,
                );
                this.$router.replace({
                    name: "manager.orders.show",
                    params: { id: result.order.id },
                });
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    e.message ||
                    "Не удалось перевести в заказ";
            } finally {
                this.saving = false;
            }
        },
        async cancel() {
            if (!this.canPromote) return;
            this.cancelling = true;
            this.error = null;
            try {
                await orderDraftService.cancel(this.draftId);
                this.$router.replace({ name: "manager.order-drafts" });
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось отменить";
            } finally {
                this.cancelling = false;
            }
        },
    },
};
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-4">
        <button
            type="button"
            class="text-sm text-[#C20A6C] hover:underline"
            @click="$router.push({ name: 'manager.order-drafts' })"
        >
            ← К заявкам
        </button>

        <p v-if="error" class="text-red-600">{{ error }}</p>

        <template v-if="draft">
            <div class="border p-4">
                <h1 class="text-xl font-semibold">Заявка #{{ draft.id }}</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ draftSourceLabel(draft.source) }} ·
                    {{ draftStatusLabel(draft.status) }}
                </p>
                <p class="mt-2">
                    {{ draft.full_name || selectedClient?.name || "—" }}
                    · {{ draft.phone || selectedClient?.phone || "—" }}
                </p>
                <p v-if="draft.comment" class="mt-2 text-sm">
                    Комментарий: {{ draft.comment }}
                </p>
                <pre
                    v-if="draft.payload?.intake_data"
                    class="mt-2 overflow-auto bg-gray-50 p-2 text-xs"
                >{{ draft.payload.intake_data }}</pre>
            </div>

            <template v-if="draft.status === 'promoted' && draft.order_id">
                <button
                    type="button"
                    class="bg-[#C20A6C] px-4 py-2 text-white"
                    @click="
                        $router.push({
                            name: 'manager.orders.show',
                            params: { id: draft.order_id },
                        })
                    "
                >
                    Открыть заказ #{{ draft.order_id }}
                </button>
            </template>

            <template v-else-if="canPromote">
                <div v-if="needsCreateClient" class="border p-4">
                    <label class="flex items-center gap-2">
                        <input v-model="createClient" type="checkbox" />
                        Создать walk-in клиента из ФИО и телефона заявки
                    </label>
                    <p v-if="!createClient" class="mt-2 text-sm text-gray-600">
                        Укажите client_id вручную ниже (через создание клиента
                        отдельно).
                    </p>
                    <label v-if="!createClient" class="mt-2 block">
                        client_id
                        <input
                            v-model="form.client_id"
                            type="number"
                            class="mt-1 w-full border px-3 py-2"
                        />
                    </label>
                </div>

                <div class="grid gap-3 border p-4 sm:grid-cols-2">
                    <label class="block">
                        Тип оплаты
                        <select
                            v-model="form.billing_type"
                            class="mt-1 w-full border px-3 py-2"
                        >
                            <option value="paid">Платный</option>
                            <option value="warranty">Гарантийный</option>
                        </select>
                    </label>
                    <label class="block">
                        Срочность
                        <select
                            v-model="form.urgency"
                            class="mt-1 w-full border px-3 py-2"
                        >
                            <option value="normal">Обычный</option>
                            <option value="urgent">Срочный</option>
                        </select>
                    </label>
                    <label class="block">
                        Оценка, ₽
                        <input
                            v-model="form.estimated_cost"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-1 w-full border px-3 py-2"
                        />
                    </label>
                    <label class="mt-6 flex items-center gap-2">
                        <input v-model="form.needs_delivery" type="checkbox" />
                        Доставка
                    </label>
                    <label v-if="form.needs_delivery" class="block sm:col-span-2">
                        Адрес
                        <input
                            v-model="form.delivery_address"
                            type="text"
                            class="mt-1 w-full border px-3 py-2"
                        />
                    </label>
                </div>

                <div class="border p-4">
                    <div class="mb-3 flex gap-3">
                        <button
                            type="button"
                            class="text-[#C20A6C]"
                            @click="addSharpening"
                        >
                            + заточка
                        </button>
                        <button
                            type="button"
                            class="text-[#C20A6C]"
                            @click="addRepair"
                        >
                            + ремонт
                        </button>
                    </div>
                    <div
                        v-for="(item, index) in form.items"
                        :key="index"
                        class="mb-3 space-y-2 border-b pb-3 last:mb-0 last:border-0"
                    >
                        <div class="flex justify-between">
                            <span>{{ KIND_LABELS[item.kind] }}</span>
                            <button
                                type="button"
                                class="text-red-600"
                                @click="removeItem(index)"
                            >
                                Убрать
                            </button>
                        </div>
                        <template v-if="item.kind === 'sharpening'">
                            <div class="grid grid-cols-[1fr_6rem] gap-2">
                                <input
                                    v-model="item.title"
                                    type="text"
                                    class="border px-3 py-2"
                                    placeholder="Название"
                                />
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    min="1"
                                    class="border px-3 py-2"
                                />
                            </div>
                        </template>
                        <template v-else>
                            <select
                                v-model="item.equipment_id"
                                class="w-full border px-3 py-2"
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
                            <textarea
                                v-model="item.problem"
                                rows="2"
                                class="w-full border px-3 py-2"
                                placeholder="Проблема"
                            />
                        </template>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="bg-[#C20A6C] px-5 py-2.5 text-white disabled:opacity-50"
                        :disabled="saving"
                        @click="promote"
                    >
                        {{ saving ? "Создание…" : "Перевести в заказ" }}
                    </button>
                    <button
                        type="button"
                        class="border border-red-600 px-5 py-2.5 text-red-600 disabled:opacity-50"
                        :disabled="cancelling"
                        @click="cancel"
                    >
                        Отменить заявку
                    </button>
                </div>
            </template>
        </template>
    </div>
</template>
