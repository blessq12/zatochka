<script>
import ClientSectionCard from "../../components/Layout/ClientSectionCard.vue";
import { equipmentService } from "../../services/EquipmentService.js";
import { KIND_LABELS } from "../../services/OrderService.js";
import { orderDraftService } from "../../services/OrderDraftService.js";

function emptySharpening() {
    return { kind: "sharpening", title: "", quantity: 1 };
}

function emptyRepair() {
    return { kind: "repair", equipment_id: "", problem: "" };
}

const fieldClass =
    "w-full max-w-full min-w-0 border border-white/20 bg-white/60 px-4 py-3.5 text-dark-gray-500 shadow-lg outline-none backdrop-blur-md transition-all duration-300 focus:border-[#C20A6C]/50 focus:ring-2 focus:ring-[#C20A6C]/30 dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200 sm:px-6 sm:py-4";

export default {
    name: "ClientOrderCreatePage",
    components: { ClientSectionCard },
    data() {
        return {
            equipments: [],
            saving: false,
            error: null,
            KIND_LABELS,
            fieldClass,
            form: {
                needs_delivery: false,
                delivery_address: "",
                items: [emptySharpening()],
            },
        };
    },
    mounted() {
        this.loadEquipments();
    },
    methods: {
        async loadEquipments() {
            try {
                this.equipments = await equipmentService.list();
            } catch {
                this.equipments = [];
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
            const hasRepair = items.some((item) => item.kind === "repair");
            return hasRepair ? "repair" : "sharpening";
        },
        async submit() {
            this.saving = true;
            this.error = null;
            try {
                if (this.form.items.length === 0) {
                    throw new Error("Добавьте хотя бы одну позицию");
                }
                for (const item of this.form.items) {
                    if (item.kind === "sharpening") {
                        if (!item.title?.trim() || !item.quantity) {
                            throw new Error(
                                "Заполните заточку: название и количество",
                            );
                        }
                    } else if (!item.equipment_id) {
                        throw new Error("Выберите оборудование для ремонта");
                    }
                }
                if (
                    this.form.needs_delivery &&
                    !this.form.delivery_address?.trim()
                ) {
                    throw new Error("Укажите адрес доставки");
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

                const draft = await orderDraftService.create({
                    service_type: this.detectServiceType(items),
                    needs_delivery: this.form.needs_delivery,
                    delivery_address: this.form.needs_delivery
                        ? this.form.delivery_address
                        : null,
                    payload: { items },
                });
                this.$router.replace({
                    name: "client.drafts.show",
                    params: { id: draft.id },
                });
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    e.message ||
                    "Не удалось создать черновик";
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<template>
    <div class="space-y-4">
        <p v-if="error" class="text-base text-red-600">{{ error }}</p>

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
                            placeholder="Название"
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
                    <select v-model="item.equipment_id" :class="fieldClass">
                        <option value="" disabled>Оборудование</option>
                        <option
                            v-for="eq in equipments"
                            :key="eq.id"
                            :value="String(eq.id)"
                        >
                            {{ eq.name }} · {{ eq.brand }}
                        </option>
                    </select>
                    <p
                        v-if="equipments.length === 0"
                        class="text-base text-dark-gray-500 dark:text-gray-400"
                    >
                        Нет оборудования в профиле — ремонт пока недоступен.
                    </p>
                    <textarea
                        v-model="item.problem"
                        rows="2"
                        :class="fieldClass"
                        placeholder="Описание проблемы"
                    />
                </template>
            </div>
        </ClientSectionCard>

        <button
            type="button"
            class="w-full bg-[#C20A6C] px-6 py-3.5 font-jost-bold text-white transition hover:bg-[#a0085a] disabled:opacity-50"
            :disabled="saving"
            @click="submit"
        >
            {{ saving ? "Отправка…" : "Отправить заявку" }}
        </button>
    </div>
</template>
