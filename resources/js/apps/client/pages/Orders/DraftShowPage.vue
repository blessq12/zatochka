<script>
import ClientSectionCard from "../../components/Layout/ClientSectionCard.vue";
import { equipmentService } from "../../services/EquipmentService.js";
import { KIND_LABELS } from "../../services/OrderService.js";
import {
    draftStatusLabel,
    orderDraftService,
} from "../../services/OrderDraftService.js";

function emptySharpening() {
    return { kind: "sharpening", title: "", quantity: 1 };
}

function emptyRepair() {
    return { kind: "repair", equipment_id: "", problem: "" };
}

const fieldClass =
    "w-full max-w-full min-w-0 border border-white/20 bg-white/60 px-4 py-3.5 text-dark-gray-500 shadow-lg outline-none backdrop-blur-md transition-all duration-300 focus:border-[#C20A6C]/50 focus:ring-2 focus:ring-[#C20A6C]/30 dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200 sm:px-6 sm:py-4";

export default {
    name: "ClientDraftShowPage",
    components: { ClientSectionCard },
    data() {
        return {
            draft: null,
            equipments: [],
            saving: false,
            cancelling: false,
            error: null,
            KIND_LABELS,
            draftStatusLabel,
            fieldClass,
            form: {
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
        canEdit() {
            return this.draft?.status === "pending";
        },
    },
    async mounted() {
        await Promise.all([this.loadEquipments(), this.load()]);
    },
    methods: {
        async loadEquipments() {
            try {
                this.equipments = await equipmentService.list();
            } catch {
                this.equipments = [];
            }
        },
        async load() {
            this.error = null;
            try {
                this.draft = await orderDraftService.get(this.draftId);
                this.form.needs_delivery = Boolean(this.draft.needs_delivery);
                this.form.delivery_address = this.draft.delivery_address || "";
                const items = this.draft.payload?.items || [];
                this.form.items =
                    items.length > 0
                        ? items.map((item) =>
                              item.kind === "repair"
                                  ? {
                                        kind: "repair",
                                        equipment_id: String(
                                            item.equipment_id || "",
                                        ),
                                        problem: item.problem || "",
                                    }
                                  : {
                                        kind: "sharpening",
                                        title: item.title || "",
                                        quantity: item.quantity || 1,
                                    },
                          )
                        : [emptySharpening()];
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Черновик не найден";
                this.draft = null;
            }
        },
        addSharpening() {
            this.form.items.push(emptySharpening());
        },
        addRepair() {
            this.form.items.push(emptyRepair());
        },
        removeItem(index) {
            this.form.items.splice(index, 1);
        },
        detectServiceType(items) {
            return items.some((item) => item.kind === "repair")
                ? "repair"
                : "sharpening";
        },
        async save() {
            if (!this.canEdit) return;
            this.saving = true;
            this.error = null;
            try {
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
                this.draft = await orderDraftService.update(this.draftId, {
                    service_type: this.detectServiceType(items),
                    needs_delivery: this.form.needs_delivery,
                    delivery_address: this.form.needs_delivery
                        ? this.form.delivery_address
                        : null,
                    payload: { items },
                });
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось сохранить";
            } finally {
                this.saving = false;
            }
        },
        async cancel() {
            if (!this.canEdit) return;
            this.cancelling = true;
            this.error = null;
            try {
                await orderDraftService.cancel(this.draftId);
                this.$router.replace({ name: "client.orders" });
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
    <div class="space-y-4">
        <p v-if="error" class="text-base text-red-600">{{ error }}</p>
        <template v-if="draft">
            <p class="text-base text-dark-gray-500 dark:text-gray-300">
                Черновик #{{ draft.id }} ·
                {{ draftStatusLabel(draft.status) }}
            </p>

            <template v-if="draft.status === 'promoted' && draft.order_id">
                <button
                    type="button"
                    class="text-base font-jost-medium text-[#C20A6C] hover:underline"
                    @click="
                        $router.push({
                            name: 'client.orders.show',
                            params: { id: draft.order_id },
                        })
                    "
                >
                    Открыть заказ #{{ draft.order_id }}
                </button>
            </template>

            <template v-else-if="canEdit">
                <ClientSectionCard title="ДОСТАВКА">
                    <label
                        class="flex items-center gap-2 text-base text-dark-gray-500 dark:text-gray-200"
                    >
                        <input v-model="form.needs_delivery" type="checkbox" />
                        Нужна доставка
                    </label>
                    <label v-if="form.needs_delivery" class="mt-4 block">
                        <span
                            class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200"
                        >
                            Адрес доставки
                        </span>
                        <input
                            v-model="form.delivery_address"
                            type="text"
                            :class="fieldClass"
                        />
                    </label>
                </ClientSectionCard>

                <ClientSectionCard title="СОСТАВ">
                    <div class="mb-4 flex flex-row flex-wrap gap-3">
                        <button
                            type="button"
                            class="text-base font-jost-medium text-[#C20A6C] hover:underline"
                            @click="addSharpening"
                        >
                            + заточка
                        </button>
                        <button
                            type="button"
                            class="text-base font-jost-medium text-[#C20A6C] hover:underline"
                            @click="addRepair"
                        >
                            + ремонт
                        </button>
                    </div>
                    <div
                        v-for="(item, index) in form.items"
                        :key="index"
                        class="mb-4 space-y-2 border-b border-dark-blue-500/10 pb-3 last:mb-0 last:border-0 last:pb-0 dark:border-white/10"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-base font-jost-medium text-dark-blue-500 dark:text-dark-blue-300"
                            >
                                {{ KIND_LABELS[item.kind] }}
                            </span>
                            <button
                                type="button"
                                class="text-base text-red-600 hover:underline"
                                @click="removeItem(index)"
                            >
                                Убрать
                            </button>
                        </div>
                        <template v-if="item.kind === 'sharpening'">
                            <div class="grid grid-cols-[1fr_4.5rem] gap-2">
                                <input
                                    v-model="item.title"
                                    type="text"
                                    :class="fieldClass"
                                />
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    min="1"
                                    :class="fieldClass"
                                />
                            </div>
                        </template>
                        <template v-else>
                            <select
                                v-model="item.equipment_id"
                                :class="fieldClass"
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
                                :class="fieldClass"
                            />
                        </template>
                    </div>
                </ClientSectionCard>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button
                        type="button"
                        class="bg-[#C20A6C] px-6 py-3.5 font-jost-bold text-white disabled:opacity-50"
                        :disabled="saving"
                        @click="save"
                    >
                        {{ saving ? "Сохранение…" : "Сохранить" }}
                    </button>
                    <button
                        type="button"
                        class="border border-red-600 px-6 py-3.5 font-jost-bold text-red-600 disabled:opacity-50"
                        :disabled="cancelling"
                        @click="cancel"
                    >
                        {{ cancelling ? "Отмена…" : "Отменить заявку" }}
                    </button>
                </div>
            </template>
        </template>
    </div>
</template>
