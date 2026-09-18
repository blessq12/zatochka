<script>
import { actorService } from "../../services/ActorService.js";
import { equipmentService } from "../../services/EquipmentService.js";
import { KIND_LABELS, URGENCY_LABELS, orderService } from "../../services/OrderService.js";

function emptySharpening() {
    return { kind: "sharpening", title: "", quantity: 1 };
}

function emptyModule() {
    return { name: "", serial_number: "" };
}

function emptyRepair(forceNewEquipment = false) {
    return {
        kind: "repair",
        equipment_id: "",
        problem: "",
        showNewEquipment: forceNewEquipment,
        newEquipment: {
            name: "",
            brand: "",
            type: "",
            modules: [emptyModule()],
        },
        creatingEquipment: false,
    };
}

export default {
    name: "OrderFormPage",
    data() {
        return {
            step: 1,
            maxReachedStep: 1,
            searchResults: [],
            searchQuery: "",
            searchTimer: null,
            searching: false,
            equipments: [],
            selectedClient: null,
            saving: false,
            creatingClient: false,
            error: null,
            KIND_LABELS,
            URGENCY_LABELS,
            newClient: {
                name: "",
                phone: "",
            },
            form: {
                client_id: this.$route.query.client_id || "",
                billing_type: "paid",
                urgency: "normal",
                estimated_cost: "",
                needs_delivery: false,
                delivery_address: "",
                items: [],
            },
        };
    },
    computed: {
        stepTitle() {
            switch (this.step) {
                case 1:
                    return "Клиент";
                case 2:
                    return "Состав заказа";
                case 3:
                    return "Стоимость и параметры";
                default:
                    return "";
            }
        },
        clientLabel() {
            if (!this.selectedClient) {
                return this.form.client_id ? `#${this.form.client_id}` : "";
            }
            return (
                [this.selectedClient.name, this.selectedClient.phone]
                    .filter(Boolean)
                    .join(" · ") || `#${this.selectedClient.id}`
            );
        },
        hasEquipments() {
            return this.equipments.length > 0;
        },
    },
    watch: {
        searchQuery(value) {
            clearTimeout(this.searchTimer);
            if (!value || !String(value).trim()) {
                this.searchResults = [];
                this.searching = false;
                return;
            }
            this.searching = true;
            this.searchTimer = setTimeout(() => {
                this.runSearch(value);
            }, 300);
        },
        "form.client_id"(value) {
            if (value) {
                this.loadEquipments(value);
            } else {
                this.equipments = [];
            }
        },
    },
    async mounted() {
        if (this.form.client_id) {
            await this.resolveSelectedClient(this.form.client_id);
            this.maxReachedStep = Math.max(this.maxReachedStep, 1);
        }
    },
    beforeUnmount() {
        clearTimeout(this.searchTimer);
    },
    methods: {
        async runSearch(value) {
            try {
                this.searchResults = await actorService.list("clients", {
                    q: String(value).trim(),
                });
            } catch {
                this.searchResults = [];
            } finally {
                this.searching = false;
            }
        },
        async loadEquipments(clientId) {
            try {
                this.equipments = await equipmentService.list(clientId);
            } catch {
                this.equipments = [];
            }
        },
        async resolveSelectedClient(id) {
            try {
                this.selectedClient = await actorService.get("clients", id);
                this.form.client_id = String(id);
            } catch {
                this.selectedClient = null;
            }
        },
        selectClient(client) {
            this.selectedClient = client;
            this.form.client_id = String(client.id);
            this.searchQuery = "";
            this.searchResults = [];
            this.error = null;
        },
        clearClient() {
            this.selectedClient = null;
            this.form.client_id = "";
            this.equipments = [];
        },
        async createWalkIn() {
            this.creatingClient = true;
            this.error = null;
            try {
                if (
                    !this.newClient.name?.trim() ||
                    !this.newClient.phone?.trim()
                ) {
                    this.error = "Укажите ФИО и телефон";
                    return;
                }
                const created = await actorService.createWalkInClient({
                    name: this.newClient.name.trim(),
                    phone: this.newClient.phone.trim(),
                });
                this.selectClient(created);
                this.newClient = { name: "", phone: "" };
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    (e.response?.data?.errors
                        ? Object.values(e.response.data.errors).flat().join(" ")
                        : "Не удалось создать клиента");
            } finally {
                this.creatingClient = false;
            }
        },
        goStep(n) {
            if (n <= this.maxReachedStep) {
                this.step = n;
                this.error = null;
            }
        },
        addSharpening() {
            this.form.items.push(emptySharpening());
        },
        addRepair() {
            this.form.items.push(emptyRepair(!this.hasEquipments));
        },
        removeItem(index) {
            this.form.items.splice(index, 1);
        },
        toggleNewEquipment(item) {
            item.showNewEquipment = !item.showNewEquipment;
        },
        showEquipmentCreate(item) {
            return !this.hasEquipments || item.showNewEquipment;
        },
        showEquipmentSelect(item) {
            return this.hasEquipments;
        },
        addModule(item) {
            item.newEquipment.modules.push(emptyModule());
        },
        removeModule(item, index) {
            item.newEquipment.modules.splice(index, 1);
            if (item.newEquipment.modules.length === 0) {
                item.newEquipment.modules.push(emptyModule());
            }
        },
        async createEquipmentForItem(item) {
            if (!this.form.client_id) {
                this.error = "Сначала выберите клиента";
                return;
            }
            const eq = item.newEquipment;
            if (!eq.name || !eq.brand || !eq.type) {
                this.error = "Заполните название, бренд и тип";
                return;
            }
            if (!eq.modules.length) {
                this.error = "Добавьте хотя бы один модуль";
                return;
            }
            for (const mod of eq.modules) {
                if (!mod.name?.trim() || !mod.serial_number?.trim()) {
                    this.error = "У модуля укажите название и серийный номер";
                    return;
                }
            }
            item.creatingEquipment = true;
            this.error = null;
            try {
                const created = await equipmentService.create({
                    client_id: Number(this.form.client_id),
                    name: eq.name,
                    brand: eq.brand,
                    type: eq.type,
                    modules: eq.modules.map((m) => ({
                        name: m.name,
                        serial_number: m.serial_number,
                    })),
                });
                await this.loadEquipments(this.form.client_id);
                item.equipment_id = String(created.id);
                item.showNewEquipment = false;
                item.newEquipment = {
                    name: "",
                    brand: "",
                    type: "",
                    modules: [emptyModule()],
                };
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    "Не удалось создать оборудование";
            } finally {
                item.creatingEquipment = false;
            }
        },
        validateStep1() {
            if (!this.form.client_id || !this.selectedClient) {
                this.error = "Выберите или создайте клиента";
                return false;
            }
            return true;
        },
        validateStep2() {
            if (this.form.items.length === 0) {
                this.error = "Добавьте хотя бы одну позицию";
                return false;
            }
            for (const item of this.form.items) {
                if (item.kind === "sharpening") {
                    if (!item.title || !item.quantity || item.quantity < 1) {
                        this.error = "Заполните все позиции заточки";
                        return false;
                    }
                } else if (!item.equipment_id) {
                    this.error = "Укажите оборудование для ремонта";
                    return false;
                }
            }
            return true;
        },
        validateStep3() {
            if (
                this.form.estimated_cost === "" ||
                Number(this.form.estimated_cost) < 0
            ) {
                this.error = "Укажите ориентировочную стоимость";
                return false;
            }
            if (
                this.form.needs_delivery &&
                !String(this.form.delivery_address || "").trim()
            ) {
                this.error = "Укажите адрес доставки";
                return false;
            }
            return true;
        },
        next() {
            this.error = null;
            if (this.step === 1 && !this.validateStep1()) return;
            if (this.step === 2 && !this.validateStep2()) return;
            if (this.step < 3) {
                this.step += 1;
                this.maxReachedStep = Math.max(this.maxReachedStep, this.step);
            }
        },
        prev() {
            this.error = null;
            if (this.step > 1) {
                this.step -= 1;
            }
        },
        buildItemsPayload() {
            return this.form.items.map((item) => {
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
        async submit() {
            this.error = null;
            if (!this.validateStep3()) return;
            this.saving = true;
            try {
                const created = await orderService.create({
                    client_id: Number(this.form.client_id),
                    billing_type: this.form.billing_type,
                    urgency: this.form.urgency,
                    estimated_cost: String(this.form.estimated_cost),
                    needs_delivery: Boolean(this.form.needs_delivery),
                    delivery_address: this.form.needs_delivery
                        ? this.form.delivery_address
                        : null,
                    items: this.buildItemsPayload(),
                });
                this.$router.push({
                    name: "manager.orders.show",
                    params: { id: String(created.id) },
                });
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    (e.response?.data?.errors
                        ? Object.values(e.response.data.errors).flat().join(" ")
                        : "Ошибка сохранения");
            } finally {
                this.saving = false;
            }
        },
        cancel() {
            this.$router.push({ name: "manager.orders" });
        },
        equipmentOptionLabel(eq) {
            const modules = eq.modules?.length
                ? ` · ${eq.modules.length} мод.`
                : "";
            return `${eq.name} · ${eq.brand}${modules}`;
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="space-y-2">
            <h1 class="app-page-title">
                Новый заказ
            </h1>
            <div class="app-tabs">
                <button
                    v-for="n in 3"
                    :key="n"
                    type="button"
                    class="app-tab"
                    :class="
                        step === n
                            ? 'border-pink-500 bg-pink-50 text-pink-600'
                            : n <= maxReachedStep
                              ? 'border-slate-200 bg-white text-slate-600 hover:border-pink-300'
                              : 'border-slate-100 bg-slate-50 text-slate-400'
                    "
                    :disabled="n > maxReachedStep"
                    @click="goStep(n)"
                >
                    {{ n }}.
                    {{ n === 1 ? "Клиент" : n === 2 ? "Состав" : "Параметры" }}
                </button>
            </div>
            <p class="text-sm text-slate-500">
                Шаг {{ step }}: {{ stepTitle }}
            </p>
        </div>

        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <!-- Step 1 -->
        <section v-if="step === 1" class="space-y-5">
            <div
                v-if="selectedClient"
                class="flex flex-wrap items-center justify-between gap-2 border border-pink-200 bg-pink-50 px-3 py-2 text-sm"
            >
                <span class="font-jost-medium text-dark-blue-500">
                    {{ clientLabel }}
                </span>
                <button
                    type="button"
                    class="text-pink-600 hover:underline"
                    @click="clearClient"
                >
                    Сменить
                </button>
            </div>

            <template v-else>
                <label class="block space-y-1">
                    <span class="text-sm text-slate-600">Поиск</span>
                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Телефон или ФИО"
                        class="w-full border border-slate-300 px-3 py-2"
                    />
                </label>
                <p v-if="searching" class="text-xs text-slate-500">Поиск…</p>
                <ul
                    v-if="searchResults.length"
                    class="divide-y divide-slate-100 border border-slate-200"
                >
                    <li
                        v-for="client in searchResults"
                        :key="client.id"
                        class="flex items-center justify-between gap-2 px-3 py-2 text-sm"
                    >
                        <span>
                            {{ client.name || "—" }}
                            <span class="text-slate-500">
                                · {{ client.phone || "без телефона" }}
                            </span>
                        </span>
                        <button
                            type="button"
                            class="text-pink-600 hover:underline"
                            @click="selectClient(client)"
                        >
                            Выбрать
                        </button>
                    </li>
                </ul>

                <div class="space-y-3 border border-slate-200 p-4">
                    <p class="text-sm font-jost-medium text-slate-700">
                        Новый клиент
                    </p>
                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">ФИО</span>
                        <input
                            v-model="newClient.name"
                            type="text"
                            class="w-full border border-slate-300 px-3 py-2"
                        />
                    </label>
                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">Телефон</span>
                        <input
                            v-model="newClient.phone"
                            type="tel"
                            class="w-full border border-slate-300 px-3 py-2"
                        />
                    </label>
                    <button
                        type="button"
                        class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                        :disabled="creatingClient"
                        @click="createWalkIn"
                    >
                        {{ creatingClient ? "Создание…" : "Создать и выбрать" }}
                    </button>
                </div>
            </template>
        </section>

        <!-- Step 2 -->
        <section v-else-if="step === 2" class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-slate-600">
                    Клиент:
                    <span class="font-jost-medium text-dark-blue-500">
                        {{ clientLabel }}
                    </span>
                </p>
                <div class="flex gap-3">
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

            <p
                v-if="form.items.length === 0"
                class="border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500"
            >
                Добавьте предмет заказа
            </p>

            <div
                v-for="(item, index) in form.items"
                :key="index"
                class="space-y-3 border border-slate-200 p-3"
            >
                <div class="flex items-center justify-between">
                    <span class="text-sm font-jost-medium text-dark-blue-500">
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
                    <label class="block space-y-1">
                        <span class="text-xs text-slate-500">Название</span>
                        <input
                            v-model="item.title"
                            type="text"
                            class="w-full border border-slate-300 px-3 py-2"
                        />
                    </label>
                    <label class="block space-y-1">
                        <span class="text-xs text-slate-500">Количество</span>
                        <input
                            v-model.number="item.quantity"
                            type="number"
                            min="1"
                            class="w-full border border-slate-300 px-3 py-2"
                        />
                    </label>
                </template>

                <template v-else>
                    <label
                        v-if="showEquipmentSelect(item)"
                        class="block space-y-1"
                    >
                        <span class="text-xs text-slate-500">Оборудование</span>
                        <select
                            v-model="item.equipment_id"
                            class="w-full border border-slate-300 px-3 py-2"
                        >
                            <option value="" disabled>Выберите</option>
                            <option
                                v-for="eq in equipments"
                                :key="eq.id"
                                :value="String(eq.id)"
                            >
                                {{ equipmentOptionLabel(eq) }}
                            </option>
                        </select>
                    </label>
                    <label class="block space-y-1">
                        <span class="text-xs text-slate-500">Проблема</span>
                        <input
                            v-model="item.problem"
                            type="text"
                            class="w-full border border-slate-300 px-3 py-2"
                        />
                    </label>

                    <button
                        v-if="hasEquipments"
                        type="button"
                        class="text-sm text-pink-600 hover:underline"
                        @click="toggleNewEquipment(item)"
                    >
                        {{
                            item.showNewEquipment
                                ? "Скрыть новое оборудование"
                                : "Новое оборудование"
                        }}
                    </button>

                    <div
                        v-if="showEquipmentCreate(item)"
                        class="space-y-3 border border-slate-200 bg-slate-50 p-3"
                    >
                        <p
                            v-if="!hasEquipments"
                            class="text-sm font-jost-medium text-slate-700"
                        >
                            Новое оборудование
                        </p>
                        <div class="grid gap-2 sm:grid-cols-3">
                            <input
                                v-model="item.newEquipment.name"
                                type="text"
                                placeholder="Название"
                                class="border border-slate-300 px-3 py-2"
                            />
                            <input
                                v-model="item.newEquipment.brand"
                                type="text"
                                placeholder="Бренд"
                                class="border border-slate-300 px-3 py-2"
                            />
                            <input
                                v-model="item.newEquipment.type"
                                type="text"
                                placeholder="Тип"
                                class="border border-slate-300 px-3 py-2"
                            />
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500"
                                    >Модули</span
                                >
                                <button
                                    type="button"
                                    class="text-xs text-pink-600 hover:underline"
                                    @click="addModule(item)"
                                >
                                    + модуль
                                </button>
                            </div>
                            <div
                                v-for="(mod, mi) in item.newEquipment.modules"
                                :key="mi"
                                class="flex flex-col gap-2 sm:flex-row sm:items-end"
                            >
                                <input
                                    v-model="mod.name"
                                    type="text"
                                    placeholder="Название модуля"
                                    class="flex-1 border border-slate-300 px-3 py-2"
                                />
                                <input
                                    v-model="mod.serial_number"
                                    type="text"
                                    placeholder="Серийный номер"
                                    class="flex-1 border border-slate-300 px-3 py-2"
                                />
                                <button
                                    type="button"
                                    class="text-sm text-red-600 hover:underline"
                                    @click="removeModule(item, mi)"
                                >
                                    Убрать
                                </button>
                            </div>
                            <p
                                v-if="item.newEquipment.modules.length === 0"
                                class="text-xs text-red-600"
                            >
                                Нужен хотя бы один модуль
                            </p>
                        </div>

                        <button
                            type="button"
                            class="bg-pink-500 px-3 py-1.5 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                            :disabled="item.creatingEquipment"
                            @click="createEquipmentForItem(item)"
                        >
                            {{
                                item.creatingEquipment
                                    ? "Сохранение…"
                                    : "Сохранить оборудование"
                            }}
                        </button>
                    </div>
                </template>
            </div>
        </section>

        <!-- Step 3 -->
        <section v-else class="space-y-4">
            <div
                class="border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600"
            >
                <p>
                    Клиент:
                    <span class="font-jost-medium text-dark-blue-500">
                        {{ clientLabel }}
                    </span>
                </p>
                <p>Позиций: {{ form.items.length }}</p>
            </div>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Тип оплаты</span>
                <select
                    v-model="form.billing_type"
                    class="w-full border border-slate-300 px-3 py-2"
                >
                    <option value="paid">Платный</option>
                    <option value="warranty">Гарантийный</option>
                </select>
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Срочность</span>
                <select
                    v-model="form.urgency"
                    class="w-full border border-slate-300 px-3 py-2"
                >
                    <option value="normal">
                        {{ URGENCY_LABELS.normal }}
                    </option>
                    <option value="urgent">
                        {{ URGENCY_LABELS.urgent }}
                    </option>
                </select>
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">
                    Ориентировочная стоимость
                </span>
                <input
                    v-model="form.estimated_cost"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input v-model="form.needs_delivery" type="checkbox" />
                Нужна доставка
            </label>

            <label v-if="form.needs_delivery" class="block space-y-1">
                <span class="text-sm text-slate-600">Адрес доставки</span>
                <input
                    v-model="form.delivery_address"
                    type="text"
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>
        </section>

        <div class="flex flex-wrap gap-3 pt-2">
            <button
                v-if="step > 1"
                type="button"
                class="border border-slate-300 px-4 py-2 text-sm text-slate-600"
                @click="prev"
            >
                Назад
            </button>
            <button
                v-if="step < 3"
                type="button"
                class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600"
                @click="next"
            >
                Далее
            </button>
            <button
                v-else
                type="button"
                class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                :disabled="saving"
                @click="submit"
            >
                {{ saving ? "Создание…" : "Создать заказ" }}
            </button>
            <button
                type="button"
                class="border border-slate-300 px-4 py-2 text-sm text-slate-600"
                @click="cancel"
            >
                Отмена
            </button>
        </div>
    </div>
</template>
