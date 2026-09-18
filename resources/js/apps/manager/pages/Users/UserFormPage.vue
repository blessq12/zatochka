<script>
import { actorService } from "../../services/ActorService.js";
import { equipmentService } from "../../services/EquipmentService.js";
import {
    orderService,
    statusLabel,
    KIND_LABELS,
    URGENCY_LABELS,
    BILLING_LABELS,
} from "../../services/OrderService.js";
import { formatOrderDate } from "../../../../shared/formatOrderDate.js";

function tabFromQuery(query) {
    if (query.tab === "equipment") return "equipment";
    if (query.tab === "orders") return "orders";
    return "data";
}

export default {
    name: "UserFormPage",
    data() {
        return {
            types: actorService.types,
            loading: false,
            saving: false,
            error: null,
            activeTab: tabFromQuery(this.$route.query),
            equipmentItems: [],
            equipmentLoading: false,
            equipmentError: null,
            equipmentLoaded: false,
            orderItems: [],
            ordersLoading: false,
            ordersError: null,
            ordersLoaded: false,
            statusLabel,
            KIND_LABELS,
            URGENCY_LABELS,
            BILLING_LABELS,
            formatOrderDate,
            form: {
                type: this.$route.query.type || this.$route.params.type || "clients",
                email: "",
                password: "",
                name: "",
                phone: "",
                birthday: "",
                delivery_address: "",
            },
        };
    },
    computed: {
        isEdit() {
            return !!this.$route.params.id;
        },
        isClientEdit() {
            return this.isEdit && this.form.type === "clients";
        },
        title() {
            if (!this.isEdit) {
                return "Новый пользователь";
            }
            if (this.isClientEdit) {
                return this.form.name
                    ? `Клиент · ${this.form.name}`
                    : `Клиент #${this.$route.params.id}`;
            }
            return "Редактирование пользователя";
        },
        showTabs() {
            return this.isClientEdit;
        },
        typeLabel() {
            return this.typeTitle(this.form.type);
        },
    },
    watch: {
        activeTab(tab) {
            if (!this.isClientEdit) {
                return;
            }
            if (tab === "equipment" && !this.equipmentLoaded) {
                this.loadEquipment();
            }
            if (tab === "orders" && !this.ordersLoaded) {
                this.loadOrders();
            }
            const query = { ...this.$route.query };
            if (tab === "data") {
                delete query.tab;
            } else {
                query.tab = tab;
            }
            this.$router.replace({ query });
        },
    },
    async mounted() {
        if (this.isEdit) {
            await this.load();
            if (this.isClientEdit) {
                await Promise.all([this.loadEquipment(), this.loadOrders()]);
            }
        }
    },
    methods: {
        typeTitle(type) {
            return actorService.typeLabel(type);
        },
        selectTab(tab) {
            this.activeTab = tab;
        },
        sectionButtonClass(tab) {
            return this.activeTab === tab
                ? "app-btn-primary app-action-btn"
                : "app-btn-secondary app-action-btn";
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                const type = this.$route.params.type;
                const item = await actorService.get(type, this.$route.params.id);
                this.form = {
                    type,
                    email: item.email || "",
                    password: "",
                    name: item.name || "",
                    phone: item.phone || "",
                    birthday: item.birthday || "",
                    delivery_address: item.delivery_address || "",
                };
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить";
            } finally {
                this.loading = false;
            }
        },
        async loadEquipment() {
            this.equipmentLoading = true;
            this.equipmentError = null;
            try {
                this.equipmentItems = await equipmentService.list(
                    this.$route.params.id,
                );
                this.equipmentLoaded = true;
            } catch (e) {
                this.equipmentError =
                    e.response?.data?.message ||
                    "Не удалось загрузить оборудование";
                this.equipmentItems = [];
            } finally {
                this.equipmentLoading = false;
            }
        },
        async loadOrders() {
            this.ordersLoading = true;
            this.ordersError = null;
            try {
                this.orderItems = await orderService.list({
                    clientId: this.$route.params.id,
                });
                this.ordersLoaded = true;
            } catch (e) {
                this.ordersError =
                    e.response?.data?.message || "Не удалось загрузить заказы";
                this.orderItems = [];
            } finally {
                this.ordersLoading = false;
            }
        },
        kindsSummary(order) {
            const kinds = [...new Set((order.items || []).map((i) => i.kind))];
            return kinds.map((k) => KIND_LABELS[k] || k).join(", ") || "—";
        },
        itemsSummary(order) {
            const rows = order.items || [];
            if (rows.length === 0) {
                return "Без позиций";
            }
            return rows
                .map((row) => {
                    if (row.kind === "sharpening") {
                        const title = row.title || "Заточка";
                        const qty =
                            row.quantity != null ? ` ×${row.quantity}` : "";
                        return `${title}${qty}`;
                    }
                    const problem = row.problem ? `: ${row.problem}` : "";
                    return row.equipment_id
                        ? `Ремонт #${row.equipment_id}${problem}`
                        : `Ремонт${problem}`;
                })
                .join("; ");
        },
        deliveryLabel(order) {
            if (!order.needs_delivery) {
                return "Без доставки";
            }
            return order.delivery_address
                ? `Доставка: ${order.delivery_address}`
                : "Доставка";
        },
        modulesSummary(item) {
            const modules = item.modules || [];
            if (modules.length === 0) {
                return "Модулей нет";
            }
            return modules
                .map((module) => `${module.name} (${module.serial_number})`)
                .join(", ");
        },
        goShowOrder(order) {
            this.$router.push({
                name: "manager.orders.show",
                params: { id: String(order.id) },
            });
        },
        goCreateEquipment() {
            this.$router.push({
                name: "manager.equipment.create",
                query: { client_id: String(this.$route.params.id) },
            });
        },
        goEditEquipment(item) {
            this.$router.push({
                name: "manager.equipment.edit",
                params: { id: String(item.id) },
            });
        },
        goAllEquipment() {
            this.$router.push({
                name: "manager.equipment",
                query: { client_id: String(this.$route.params.id) },
            });
        },
        async removeEquipment(item) {
            if (!confirm(`Удалить «${item.name}»?`)) {
                return;
            }
            try {
                await equipmentService.remove(item.id);
                await this.loadEquipment();
            } catch (e) {
                this.equipmentError =
                    e.response?.data?.message || "Не удалось удалить";
            }
        },
        async submit() {
            this.saving = true;
            this.error = null;
            try {
                if (this.isEdit) {
                    await actorService.update(
                        this.form.type,
                        this.$route.params.id,
                        {
                            name: this.form.name || null,
                            phone: this.form.phone || null,
                            birthday: this.form.birthday || null,
                            delivery_address: this.form.delivery_address || null,
                        },
                    );
                } else {
                    if (
                        !this.form.email ||
                        !this.form.password ||
                        !this.form.name
                    ) {
                        this.error = "Укажите имя, email и пароль";
                        return;
                    }
                    await actorService.create(this.form.type, {
                        email: this.form.email,
                        password: this.form.password,
                        name: this.form.name,
                        phone: this.form.phone || null,
                        birthday: this.form.birthday || null,
                        delivery_address: this.form.delivery_address || null,
                    });
                }
                this.$router.push({
                    name: "manager.users",
                    query: { type: this.form.type },
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
            this.$router.push({
                name: "manager.users",
                query: { type: this.form.type },
            });
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">{{ title }}</h1>
            <button
                type="button"
                class="app-btn-ghost w-full sm:w-auto"
                @click="cancel"
            >
                К списку
            </button>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p
            v-if="error && (!showTabs || activeTab === 'data')"
            class="text-sm text-red-600"
        >
            {{ error }}
        </p>

        <!-- Создание / не-клиент: одна колонка -->
        <form
            v-if="!loading && !showTabs"
            class="max-w-xl space-y-4"
            @submit.prevent="submit"
        >
            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Тип</span>
                <select
                    v-model="form.type"
                    class="app-field"
                    :disabled="isEdit"
                >
                    <option v-for="t in types" :key="t" :value="t">
                        {{ typeTitle(t) }}
                    </option>
                </select>
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Имя</span>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="app-field"
                />
            </label>

            <label v-if="!isEdit" class="block space-y-1">
                <span class="text-sm text-slate-600">Email</span>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    class="app-field"
                />
            </label>

            <label v-else class="block space-y-1">
                <span class="text-sm text-slate-600">Email</span>
                <input
                    :value="form.email"
                    type="email"
                    disabled
                    class="app-field bg-slate-50 text-slate-500"
                />
            </label>

            <label v-if="!isEdit" class="block space-y-1">
                <span class="text-sm text-slate-600">Пароль</span>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    minlength="8"
                    class="app-field"
                />
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Телефон</span>
                <input v-model="form.phone" type="tel" class="app-field" />
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">День рождения</span>
                <input v-model="form.birthday" type="date" class="app-field" />
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Адрес доставки</span>
                <input
                    v-model="form.delivery_address"
                    type="text"
                    class="app-field"
                />
            </label>

            <div class="app-actions pt-1">
                <button
                    type="submit"
                    class="app-btn-primary"
                    :disabled="saving"
                >
                    {{ saving ? "Сохранение…" : "Сохранить" }}
                </button>
                <button
                    type="button"
                    class="app-btn-ghost"
                    @click="cancel"
                >
                    Отмена
                </button>
            </div>
        </form>

        <!-- Клиент: две колонки как у заказа -->
        <template v-if="showTabs && !loading">
            <div
                class="grid gap-4 lg:grid-cols-[minmax(16rem,20rem)_minmax(0,1fr)] lg:items-start lg:gap-6"
            >
                <aside class="space-y-3 lg:sticky lg:top-4">
                    <div
                        class="space-y-2 border border-slate-300 bg-white p-3 text-sm shadow-sm lg:p-4"
                    >
                        <p class="font-jost-medium text-dark-blue-500">
                            {{ form.name || "Без имени" }}
                        </p>
                        <dl class="space-y-1.5">
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Тип</dt>
                                <dd class="text-right text-slate-800">
                                    {{ typeLabel }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Email</dt>
                                <dd class="text-right text-slate-800 break-all">
                                    {{ form.email || "—" }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Телефон</dt>
                                <dd class="text-right text-slate-800">
                                    {{ form.phone || "—" }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">ДР</dt>
                                <dd class="text-right text-slate-800">
                                    {{ form.birthday || "—" }}
                                </dd>
                            </div>
                        </dl>
                        <p
                            v-if="form.delivery_address"
                            class="border-t border-slate-100 pt-2 text-xs text-slate-500"
                        >
                            Адрес: {{ form.delivery_address }}
                        </p>
                        <p class="text-xs text-slate-500">
                            #{{ $route.params.id }}
                        </p>
                    </div>

                    <section
                        class="space-y-2 border border-slate-300 bg-white p-3 shadow-sm lg:p-4"
                    >
                        <h2 class="text-sm font-jost-bold text-dark-blue-500">
                            Разделы
                        </h2>
                        <div class="flex flex-col gap-2">
                            <button
                                type="button"
                                :class="sectionButtonClass('data')"
                                @click="selectTab('data')"
                            >
                                <span class="app-action-btn-title">Данные</span>
                                <span class="app-action-btn-hint">
                                    Профиль клиента
                                </span>
                            </button>
                            <button
                                type="button"
                                :class="sectionButtonClass('equipment')"
                                @click="selectTab('equipment')"
                            >
                                <span class="app-action-btn-title">
                                    Оборудование
                                </span>
                                <span class="app-action-btn-hint">
                                    {{
                                        equipmentLoaded
                                            ? `${equipmentItems.length} шт.`
                                            : "Каталог техники"
                                    }}
                                </span>
                            </button>
                            <button
                                type="button"
                                :class="sectionButtonClass('orders')"
                                @click="selectTab('orders')"
                            >
                                <span class="app-action-btn-title">Заказы</span>
                                <span class="app-action-btn-hint">
                                    {{
                                        ordersLoaded
                                            ? `${orderItems.length} шт.`
                                            : "Хронология"
                                    }}
                                </span>
                            </button>
                        </div>
                    </section>
                </aside>

                <div class="min-w-0 space-y-4">
                    <form
                        v-if="activeTab === 'data'"
                        class="space-y-4 border border-slate-300 bg-white p-3 shadow-sm sm:p-4"
                        @submit.prevent="submit"
                    >
                        <h2
                            class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                        >
                            Данные
                        </h2>

                        <label class="block space-y-1">
                            <span class="text-sm text-slate-600">Имя</span>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="app-field"
                            />
                        </label>

                        <label class="block space-y-1">
                            <span class="text-sm text-slate-600">Email</span>
                            <input
                                :value="form.email"
                                type="email"
                                disabled
                                class="app-field bg-slate-50 text-slate-500"
                            />
                        </label>

                        <label class="block space-y-1">
                            <span class="text-sm text-slate-600">Телефон</span>
                            <input
                                v-model="form.phone"
                                type="tel"
                                class="app-field"
                            />
                        </label>

                        <label class="block space-y-1">
                            <span class="text-sm text-slate-600"
                                >День рождения</span
                            >
                            <input
                                v-model="form.birthday"
                                type="date"
                                class="app-field"
                            />
                        </label>

                        <label class="block space-y-1">
                            <span class="text-sm text-slate-600"
                                >Адрес доставки</span
                            >
                            <input
                                v-model="form.delivery_address"
                                type="text"
                                class="app-field"
                            />
                        </label>

                        <div class="app-actions pt-1">
                            <button
                                type="submit"
                                class="app-btn-primary"
                                :disabled="saving"
                            >
                                {{ saving ? "Сохранение…" : "Сохранить" }}
                            </button>
                        </div>
                    </form>

                    <section
                        v-if="activeTab === 'equipment'"
                        class="space-y-3"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <h2
                                class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                            >
                                Оборудование
                            </h2>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="app-btn-ghost"
                                    @click="goAllEquipment"
                                >
                                    Весь список
                                </button>
                                <button
                                    type="button"
                                    class="app-btn-primary"
                                    @click="goCreateEquipment"
                                >
                                    Добавить
                                </button>
                            </div>
                        </div>

                        <p
                            v-if="equipmentLoading"
                            class="text-sm text-slate-500"
                        >
                            Загрузка…
                        </p>
                        <p
                            v-if="equipmentError"
                            class="text-sm text-red-600"
                        >
                            {{ equipmentError }}
                        </p>

                        <template v-if="!equipmentLoading">
                            <div class="app-card-list">
                                <p
                                    v-if="equipmentItems.length === 0"
                                    class="app-card text-slate-500"
                                >
                                    У клиента пока нет оборудования
                                </p>
                                <div
                                    v-for="item in equipmentItems"
                                    :key="item.id"
                                    class="app-card"
                                >
                                    <div
                                        class="flex items-start justify-between gap-2"
                                    >
                                        <div
                                            class="font-jost-medium text-dark-blue-500"
                                        >
                                            {{ item.name }}
                                        </div>
                                        <span class="text-xs text-slate-500"
                                            >#{{ item.id }}</span
                                        >
                                    </div>
                                    <p class="text-sm text-slate-700">
                                        {{ item.brand }} · {{ item.type }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Модули ({{ item.modules?.length || 0 }}):
                                        {{ modulesSummary(item) }}
                                    </p>
                                    <div class="app-actions pt-1">
                                        <button
                                            type="button"
                                            class="app-btn-secondary"
                                            @click="goEditEquipment(item)"
                                        >
                                            Изменить
                                        </button>
                                        <button
                                            type="button"
                                            class="app-btn-danger"
                                            @click="removeEquipment(item)"
                                        >
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="app-table-wrap">
                                <table class="min-w-full text-left text-sm">
                                    <thead
                                        class="border-b border-slate-200 bg-slate-50 text-slate-700"
                                    >
                                        <tr>
                                            <th class="px-4 py-3 font-jost-medium">
                                                #
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Название
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Бренд
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Тип
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Модули
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium" />
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="equipmentItems.length === 0">
                                            <td
                                                colspan="6"
                                                class="px-4 py-6 text-slate-500"
                                            >
                                                У клиента пока нет оборудования
                                            </td>
                                        </tr>
                                        <tr
                                            v-for="item in equipmentItems"
                                            :key="item.id"
                                            class="border-t border-slate-100"
                                        >
                                            <td class="px-4 py-3">
                                                {{ item.id }}
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ item.name }}
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ item.brand }}
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ item.type }}
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ modulesSummary(item) }}
                                            </td>
                                            <td
                                                class="space-x-2 whitespace-nowrap px-4 py-3 text-right"
                                            >
                                                <button
                                                    type="button"
                                                    class="text-pink-700 hover:underline"
                                                    @click="goEditEquipment(item)"
                                                >
                                                    Изменить
                                                </button>
                                                <button
                                                    type="button"
                                                    class="text-red-700 hover:underline"
                                                    @click="removeEquipment(item)"
                                                >
                                                    Удалить
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </section>

                    <section v-if="activeTab === 'orders'" class="space-y-3">
                        <h2
                            class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                        >
                            Заказы
                        </h2>

                        <p
                            v-if="ordersLoading"
                            class="text-sm text-slate-500"
                        >
                            Загрузка…
                        </p>
                        <p v-if="ordersError" class="text-sm text-red-600">
                            {{ ordersError }}
                        </p>

                        <template v-if="!ordersLoading">
                            <div class="app-card-list">
                                <p
                                    v-if="orderItems.length === 0"
                                    class="app-card text-slate-500"
                                >
                                    Заказов пока нет
                                </p>
                                <button
                                    v-for="order in orderItems"
                                    :key="order.id"
                                    type="button"
                                    class="app-card w-full text-left"
                                    @click="goShowOrder(order)"
                                >
                                    <div
                                        class="flex items-start justify-between gap-2"
                                    >
                                        <span
                                            class="font-jost-medium text-dark-blue-500"
                                        >
                                            Заказ #{{ order.id }}
                                        </span>
                                        <span class="text-xs text-pink-700"
                                            >Открыть</span
                                        >
                                    </div>
                                    <p class="text-sm text-slate-700">
                                        {{ statusLabel(order.status) }}
                                        ·
                                        {{
                                            URGENCY_LABELS[order.urgency] ||
                                            order.urgency
                                        }}
                                        ·
                                        {{
                                            BILLING_LABELS[order.billing_type] ||
                                            order.billing_type
                                        }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Создан
                                        {{ formatOrderDate(order.created_at) }}
                                        · выдан
                                        {{ formatOrderDate(order.issued_at) }}
                                    </p>
                                    <p class="text-sm text-slate-600">
                                        Оценка {{ order.estimated_cost }} ₽
                                        · мастер
                                        {{
                                            order.master_id
                                                ? `#${order.master_id}`
                                                : "не назначен"
                                        }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ itemsSummary(order) }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ kindsSummary(order) }}
                                        · позиций
                                        {{ (order.items || []).length }}
                                        · {{ deliveryLabel(order) }}
                                    </p>
                                </button>
                            </div>

                            <div class="app-table-wrap">
                                <table class="min-w-full text-left text-sm">
                                    <thead
                                        class="border-b border-slate-200 bg-slate-50 text-slate-700"
                                    >
                                        <tr>
                                            <th class="px-4 py-3 font-jost-medium">
                                                #
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Создан
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Выдан
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Статус
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Оплата
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Срочность
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Оценка
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium">
                                                Состав
                                            </th>
                                            <th class="px-4 py-3 font-jost-medium" />
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="orderItems.length === 0">
                                            <td
                                                colspan="9"
                                                class="px-4 py-6 text-slate-500"
                                            >
                                                Заказов пока нет
                                            </td>
                                        </tr>
                                        <tr
                                            v-for="order in orderItems"
                                            :key="order.id"
                                            class="border-t border-slate-100"
                                        >
                                            <td class="px-4 py-3">
                                                {{ order.id }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                {{
                                                    formatOrderDate(
                                                        order.created_at
                                                    )
                                                }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                {{
                                                    formatOrderDate(
                                                        order.issued_at
                                                    )
                                                }}
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ statusLabel(order.status) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                {{
                                                    BILLING_LABELS[
                                                        order.billing_type
                                                    ] || order.billing_type
                                                }}
                                            </td>
                                            <td class="px-4 py-3">
                                                {{
                                                    URGENCY_LABELS[
                                                        order.urgency
                                                    ] || order.urgency
                                                }}
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ order.estimated_cost }} ₽
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ itemsSummary(order) }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    class="text-pink-700 hover:underline"
                                                    @click="goShowOrder(order)"
                                                >
                                                    Открыть
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </section>
                </div>
            </div>
        </template>
    </div>
</template>
