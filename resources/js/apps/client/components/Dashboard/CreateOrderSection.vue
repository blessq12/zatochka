<script>
import { mapStores } from "pinia";
import { equipmentService } from "../../services/EquipmentService.js";
import { useOrderStore } from "../../stores/orderStore.js";

function emptySharpening() {
    return { kind: "sharpening", title: "", quantity: 1 };
}

function emptyRepair() {
    return { kind: "repair", equipment_id: "", problem: "" };
}

export default {
    name: "CreateOrderSection",
    emits: ["created"],
    data() {
        return {
            equipments: [],
            saving: false,
            error: null,
            success: null,
            form: {
                billing_type: "paid",
                urgency: "normal",
                needs_delivery: false,
                delivery_address: "",
                items: [emptySharpening()],
            },
        };
    },
    computed: {
        ...mapStores(useOrderStore),
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
        async submit() {
            this.saving = true;
            this.error = null;
            this.success = null;
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

                const payload = {
                    billing_type: this.form.billing_type,
                    urgency: this.form.urgency,
                    needs_delivery: this.form.needs_delivery,
                    delivery_address: this.form.needs_delivery
                        ? this.form.delivery_address
                        : null,
                    items: this.form.items.map((item) => {
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
                    }),
                };

                const order = await this.orderStore.createOrder(payload);
                this.success = `Заказ №${order.id} создан`;
                this.form.items = [emptySharpening()];
                this.$emit("created");
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    e.message ||
                    "Не удалось создать заказ";
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <div
            class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
        >
            <h2
                class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[90%] px-4 sm:px-6 bg-white dark:bg-dark-blue-500 text-lg sm:text-xl font-jost-bold text-[#C20A6C] text-center whitespace-nowrap"
            >
                НОВЫЙ ЗАКАЗ
            </h2>

            <p v-if="error" class="mt-4 text-red-600">{{ error }}</p>
            <p v-if="success" class="mt-4 text-green-700">{{ success }}</p>

            <div class="mt-4 space-y-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-jost-medium">
                        Тип оплаты
                        <select
                            v-model="form.billing_type"
                            class="mt-1 w-full border border-dark-blue-500/30 bg-white px-3 py-3 dark:bg-gray-800"
                        >
                            <option value="paid">Платный</option>
                            <option value="warranty">Гарантийный</option>
                        </select>
                    </label>
                    <label class="block text-sm font-jost-medium">
                        Срочность
                        <select
                            v-model="form.urgency"
                            class="mt-1 w-full border border-dark-blue-500/30 bg-white px-3 py-3 dark:bg-gray-800"
                        >
                            <option value="normal">Обычный</option>
                            <option value="urgent">Срочный</option>
                        </select>
                    </label>
                </div>

                <label class="flex items-center gap-2 text-sm font-jost-medium">
                    <input v-model="form.needs_delivery" type="checkbox" />
                    Нужна доставка
                </label>
                <label
                    v-if="form.needs_delivery"
                    class="block text-sm font-jost-medium"
                >
                    Адрес доставки
                    <input
                        v-model="form.delivery_address"
                        type="text"
                        class="mt-1 w-full border border-dark-blue-500/30 bg-white px-3 py-3 dark:bg-gray-800"
                    />
                </label>

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="font-jost-bold text-dark-blue-500">Состав</p>
                    <div class="flex gap-4">
                        <button
                            type="button"
                            class="text-[#C3006B] hover:underline"
                            @click="addSharpening"
                        >
                            + заточка
                        </button>
                        <button
                            type="button"
                            class="text-[#C3006B] hover:underline"
                            @click="addRepair"
                        >
                            + ремонт
                        </button>
                    </div>
                </div>

                <div
                    v-for="(item, index) in form.items"
                    :key="index"
                    class="space-y-3 border border-dark-blue-500/20 p-4"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-jost-medium">
                            {{
                                item.kind === "sharpening"
                                    ? "Заточка"
                                    : "Ремонт"
                            }}
                        </span>
                        <button
                            type="button"
                            class="text-red-600 hover:underline"
                            @click="removeItem(index)"
                        >
                            Убрать
                        </button>
                    </div>

                    <template v-if="item.kind === 'sharpening'">
                        <div class="grid gap-3 sm:grid-cols-[1fr_6rem]">
                            <input
                                v-model="item.title"
                                type="text"
                                placeholder="Название"
                                class="w-full border border-dark-blue-500/30 bg-white px-3 py-3 dark:bg-gray-800"
                            />
                            <input
                                v-model.number="item.quantity"
                                type="number"
                                min="1"
                                class="w-full border border-dark-blue-500/30 bg-white px-3 py-3 dark:bg-gray-800"
                            />
                        </div>
                    </template>
                    <template v-else>
                        <select
                            v-model="item.equipment_id"
                            class="w-full border border-dark-blue-500/30 bg-white px-3 py-3 dark:bg-gray-800"
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
                        <p
                            v-if="equipments.length === 0"
                            class="text-sm text-dark-gray-400"
                        >
                            Нет оборудования — ремонт пока недоступен.
                        </p>
                        <textarea
                            v-model="item.problem"
                            rows="2"
                            placeholder="Описание проблемы"
                            class="w-full border border-dark-blue-500/30 bg-white px-3 py-3 dark:bg-gray-800"
                        />
                    </template>
                </div>

                <button
                    type="button"
                    class="bg-[#C3006B] px-6 py-3 font-jost-bold text-white hover:bg-[#A8005A] disabled:opacity-50"
                    :disabled="saving"
                    @click="submit"
                >
                    {{ saving ? "Создание…" : "Создать заказ" }}
                </button>
            </div>
        </div>
    </div>
</template>
