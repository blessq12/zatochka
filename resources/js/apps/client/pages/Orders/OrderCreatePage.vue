<script>
import { equipmentService } from "../../services/EquipmentService.js";
import { KIND_LABELS, orderService } from "../../services/OrderService.js";

function emptySharpening() {
    return { kind: "sharpening", title: "", quantity: 1 };
}

function emptyRepair() {
    return { kind: "repair", equipment_id: "", problem: "" };
}

export default {
    name: "ClientOrderCreatePage",
    data() {
        return {
            equipments: [],
            saving: false,
            error: null,
            KIND_LABELS,
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
                            throw new Error("Заполните заточку: название и количество");
                        }
                    } else if (!item.equipment_id) {
                        throw new Error("Выберите оборудование для ремонта");
                    }
                }
                if (this.form.needs_delivery && !this.form.delivery_address?.trim()) {
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
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Новый заказ</h1>
            <button
                type="button"
                class="app-btn-ghost w-full sm:w-auto"
                @click="$router.push({ name: 'client.orders' })"
            >
                Отмена
            </button>
        </div>

        <p v-if="error" class="text-base text-red-600">{{ error }}</p>

        <section class="app-panel space-y-3">
            <h2 class="text-base font-jost-bold text-dark-blue-500">Параметры</h2>
            <div class="app-grid-2">
                <label class="block text-base">
                    Тип оплаты
                    <select v-model="form.billing_type" class="app-field mt-1">
                        <option value="paid">Платный</option>
                        <option value="warranty">Гарантийный</option>
                    </select>
                </label>
                <label class="block text-base">
                    Срочность
                    <select v-model="form.urgency" class="app-field mt-1">
                        <option value="normal">Обычный</option>
                        <option value="urgent">Срочный</option>
                    </select>
                </label>
            </div>
            <label class="flex items-center gap-2 text-base">
                <input v-model="form.needs_delivery" type="checkbox" />
                Нужна доставка
            </label>
            <label v-if="form.needs_delivery" class="block text-base">
                Адрес доставки
                <input
                    v-model="form.delivery_address"
                    type="text"
                    class="app-field mt-1"
                />
            </label>
        </section>

        <section class="app-panel space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-base font-jost-bold text-dark-blue-500">Состав</h2>
                <div class="flex gap-3">
                    <button
                        type="button"
                        class="text-base text-pink-600 hover:underline"
                        @click="addSharpening"
                    >
                        + заточка
                    </button>
                    <button
                        type="button"
                        class="text-base text-pink-600 hover:underline"
                        @click="addRepair"
                    >
                        + ремонт
                    </button>
                </div>
            </div>

            <div
                v-for="(item, index) in form.items"
                :key="index"
                class="space-y-2 border border-slate-200 bg-white p-3"
            >
                <div class="flex items-center justify-between">
                    <span class="font-jost-medium">
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
                    <div class="grid gap-2 sm:grid-cols-[1fr_6rem]">
                        <input
                            v-model="item.title"
                            type="text"
                            placeholder="Название"
                            class="app-field"
                        />
                        <input
                            v-model.number="item.quantity"
                            type="number"
                            min="1"
                            class="app-field"
                        />
                    </div>
                </template>
                <template v-else>
                    <select v-model="item.equipment_id" class="app-field">
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
                        class="text-base text-slate-500"
                    >
                        Нет оборудования в профиле — ремонт пока недоступен.
                        Обратитесь в мастерскую.
                    </p>
                    <textarea
                        v-model="item.problem"
                        rows="2"
                        class="app-field"
                        placeholder="Описание проблемы"
                    />
                </template>
            </div>
        </section>

        <button
            type="button"
            class="app-btn-primary w-full sm:w-auto"
            :disabled="saving"
            @click="submit"
        >
            {{ saving ? "Создание…" : "Создать заказ" }}
        </button>
    </div>
</template>
