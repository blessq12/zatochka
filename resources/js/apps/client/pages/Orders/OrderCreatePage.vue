<script>
import ClientSectionCard from "../../components/Layout/ClientSectionCard.vue";
import { equipmentService } from "../../services/EquipmentService.js";
import { KIND_LABELS, orderService } from "../../services/OrderService.js";

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
                billing_type: "paid",
                urgency: "normal",
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

                const order = await orderService.create(payload);
                this.$router.replace({
                    name: "client.orders.show",
                    params: { id: order.id },
                });
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
    <div class="space-y-5 lg:space-y-6">
        <div class="flex items-center justify-end lg:justify-between">
            <h2
                class="hidden font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 lg:block lg:text-2xl"
            >
                Новый заказ
            </h2>
            <button
                type="button"
                class="text-base font-jost-medium text-dark-gray-500 hover:text-[#C20A6C] dark:text-gray-200 lg:border lg:border-dark-blue-500/30 lg:px-4 lg:py-3 lg:hover:bg-white/60"
                @click="$router.push({ name: 'client.orders' })"
            >
                Отмена
            </button>
        </div>

        <p v-if="error" class="text-base text-red-600 lg:text-base">{{ error }}</p>

        <ClientSectionCard title="ПАРАМЕТРЫ">
            <div class="grid gap-4 sm:grid-cols-2 lg:gap-4">
                <label class="block">
                    <span
                        class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
                    >
                        Тип оплаты
                    </span>
                    <select v-model="form.billing_type" :class="fieldClass">
                        <option value="paid">Платный</option>
                        <option value="warranty">Гарантийный</option>
                    </select>
                </label>
                <label class="block">
                    <span
                        class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
                    >
                        Срочность
                    </span>
                    <select v-model="form.urgency" :class="fieldClass">
                        <option value="normal">Обычный</option>
                        <option value="urgent">Срочный</option>
                    </select>
                </label>
            </div>
            <label
                class="mt-4 flex items-center gap-2 text-base text-dark-gray-500 dark:text-gray-200 lg:mt-4 lg:text-base"
            >
                <input v-model="form.needs_delivery" type="checkbox" />
                Нужна доставка
            </label>
            <label v-if="form.needs_delivery" class="mt-4 block lg:mt-4">
                <span
                    class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
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
            <div class="mb-4 flex flex-row flex-wrap gap-3 lg:mb-4">
                <button
                    type="button"
                    class="text-base font-jost-medium text-[#C20A6C] hover:underline lg:text-base"
                    @click="addSharpening"
                >
                    + заточка
                </button>
                <button
                    type="button"
                    class="text-base font-jost-medium text-[#C20A6C] hover:underline lg:text-base"
                    @click="addRepair"
                >
                    + ремонт
                </button>
            </div>

            <div
                v-for="(item, index) in form.items"
                :key="index"
                class="mb-4 space-y-2 border-b border-dark-blue-500/10 pb-3 last:mb-0 last:border-0 last:pb-0 dark:border-white/10 lg:mb-4 lg:space-y-3 lg:border lg:border-white/20 lg:bg-white/60 lg:p-4 lg:backdrop-blur-md lg:last:mb-0 dark:lg:border-gray-700/20 dark:lg:bg-gray-800/60"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-base font-jost-medium text-dark-blue-500 dark:text-dark-blue-300 lg:text-base"
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
                    <div class="grid grid-cols-[1fr_4.5rem] gap-2 lg:grid-cols-[1fr_6rem] lg:gap-3">
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
            class="w-full bg-[#C20A6C] px-6 py-3.5 font-jost-bold text-white transition hover:bg-[#a0085a] disabled:opacity-50 lg:w-auto lg:px-8 lg:py-4 lg:text-lg"
            :disabled="saving"
            @click="submit"
        >
            {{ saving ? "Создание…" : "Создать заказ" }}
        </button>
    </div>
</template>
