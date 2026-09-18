<script>
import { equipmentService } from "../../services/EquipmentService.js";
import { actorService } from "../../services/ActorService.js";

export default {
    name: "EquipmentFormPage",
    data() {
        return {
            clients: [],
            loading: false,
            saving: false,
            error: null,
            form: {
                client_id: this.$route.query.client_id || "",
                name: "",
                brand: "",
                type: "",
                modules: [{ name: "", serial_number: "" }],
            },
        };
    },
    computed: {
        isEdit() {
            return !!this.$route.params.id;
        },
        title() {
            if (!this.isEdit) {
                return "Новое оборудование";
            }
            return this.form.name
                ? this.form.name
                : `Оборудование #${this.$route.params.id}`;
        },
        clientLabel() {
            if (!this.form.client_id) {
                return "—";
            }
            const client = this.clients.find(
                (c) => Number(c.id) === Number(this.form.client_id),
            );
            return client?.name || client?.email || `#${this.form.client_id}`;
        },
    },
    async mounted() {
        await this.loadClients();
        if (this.isEdit) {
            await this.load();
        }
    },
    methods: {
        async loadClients() {
            try {
                this.clients = await actorService.list("clients");
            } catch {
                this.clients = [];
            }
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                const item = await equipmentService.get(this.$route.params.id);
                this.form = {
                    client_id: String(item.client_id),
                    name: item.name || "",
                    brand: item.brand || "",
                    type: item.type || "",
                    modules: (item.modules || []).map((m) => ({
                        id: m.id ?? null,
                        name: m.name || "",
                        serial_number: m.serial_number || "",
                    })),
                };
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить";
            } finally {
                this.loading = false;
            }
        },
        addModule() {
            this.form.modules.push({ name: "", serial_number: "" });
        },
        removeModule(index) {
            this.form.modules.splice(index, 1);
            if (this.form.modules.length === 0) {
                this.form.modules.push({ name: "", serial_number: "" });
            }
        },
        async submit() {
            this.saving = true;
            this.error = null;
            try {
                if (!this.form.modules.length) {
                    this.error = "Добавьте хотя бы один модуль";
                    return;
                }
                for (const mod of this.form.modules) {
                    if (!mod.name?.trim() || !mod.serial_number?.trim()) {
                        this.error =
                            "У модуля укажите название и серийный номер";
                        return;
                    }
                }

                const payload = {
                    name: this.form.name,
                    brand: this.form.brand,
                    type: this.form.type,
                    modules: this.form.modules.map((m) => {
                        const row = {
                            name: m.name,
                            serial_number: m.serial_number,
                        };
                        if (m.id != null) {
                            row.id = Number(m.id);
                        }
                        return row;
                    }),
                };

                if (this.isEdit) {
                    await equipmentService.update(this.$route.params.id, payload);
                } else {
                    if (!this.form.client_id) {
                        this.error = "Выберите клиента";
                        return;
                    }
                    await equipmentService.create({
                        ...payload,
                        client_id: Number(this.form.client_id),
                    });
                }

                this.$router.push({
                    name: "manager.equipment",
                    query: this.form.client_id
                        ? { client_id: this.form.client_id }
                        : {},
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
                name: "manager.equipment",
                query: this.form.client_id
                    ? { client_id: this.form.client_id }
                    : {},
            });
        },
        goClient() {
            if (!this.form.client_id) {
                return;
            }
            this.$router.push({
                name: "manager.users.edit",
                params: { type: "clients", id: String(this.form.client_id) },
                query: { tab: "equipment" },
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
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <form
            v-if="!loading"
            class="grid gap-4 lg:grid-cols-[minmax(16rem,20rem)_minmax(0,1fr)] lg:items-start lg:gap-6"
            @submit.prevent="submit"
        >
            <aside class="space-y-3 lg:sticky lg:top-4">
                <div
                    class="space-y-2 border border-slate-300 bg-white p-3 text-sm shadow-sm lg:p-4"
                >
                    <p class="font-jost-medium text-dark-blue-500">
                        {{ form.name || "Без названия" }}
                    </p>
                    <dl class="space-y-1.5">
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">Клиент</dt>
                            <dd class="text-right text-slate-800">
                                {{ clientLabel }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">Бренд</dt>
                            <dd class="text-right text-slate-800">
                                {{ form.brand || "—" }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">Тип</dt>
                            <dd class="text-right text-slate-800">
                                {{ form.type || "—" }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-slate-500">Модули</dt>
                            <dd class="text-right text-slate-800">
                                {{ form.modules.length }}
                            </dd>
                        </div>
                    </dl>
                    <p
                        v-if="isEdit"
                        class="border-t border-slate-100 pt-2 text-xs text-slate-500"
                    >
                        #{{ $route.params.id }}
                    </p>
                </div>

                <section
                    class="space-y-3 border border-slate-300 bg-white p-3 shadow-sm lg:p-4"
                >
                    <h2 class="text-sm font-jost-bold text-dark-blue-500">
                        Сводка
                    </h2>

                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">Клиент</span>
                        <select
                            v-model="form.client_id"
                            class="app-field"
                            :disabled="isEdit"
                            required
                        >
                            <option value="" disabled>Выберите клиента</option>
                            <option
                                v-for="client in clients"
                                :key="client.id"
                                :value="String(client.id)"
                            >
                                {{
                                    client.name ||
                                    client.email ||
                                    `#${client.id}`
                                }}
                            </option>
                        </select>
                    </label>

                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">Название</span>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="app-field"
                        />
                    </label>

                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">Бренд</span>
                        <input
                            v-model="form.brand"
                            type="text"
                            required
                            class="app-field"
                        />
                    </label>

                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">Тип</span>
                        <input
                            v-model="form.type"
                            type="text"
                            required
                            class="app-field"
                        />
                    </label>

                    <div class="flex flex-col gap-2 pt-1">
                        <button
                            type="submit"
                            class="app-btn-primary w-full"
                            :disabled="saving"
                        >
                            {{ saving ? "Сохранение…" : "Сохранить" }}
                        </button>
                        <button
                            v-if="isEdit && form.client_id"
                            type="button"
                            class="app-btn-secondary w-full"
                            @click="goClient"
                        >
                            К клиенту
                        </button>
                        <button
                            type="button"
                            class="app-btn-ghost w-full"
                            @click="cancel"
                        >
                            Отмена
                        </button>
                    </div>
                </section>
            </aside>

            <div class="min-w-0 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2
                        class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                    >
                        Модули
                    </h2>
                    <button
                        type="button"
                        class="text-sm text-pink-700 hover:underline"
                        @click="addModule"
                    >
                        + модуль
                    </button>
                </div>

                <p
                    v-if="form.modules.length === 0"
                    class="border border-red-200 bg-red-50 px-3 py-4 text-sm text-red-700 shadow-sm"
                >
                    Нужен хотя бы один модуль
                </p>

                <div
                    v-for="(module, index) in form.modules"
                    :key="index"
                    class="space-y-2 border border-slate-300 bg-white p-3 shadow-sm"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-jost-medium text-dark-blue-500">
                            Модуль {{ index + 1 }}
                        </span>
                        <button
                            type="button"
                            class="text-sm text-red-700 hover:underline"
                            @click="removeModule(index)"
                        >
                            Убрать
                        </button>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="block space-y-1">
                            <span class="text-xs text-slate-500">Название</span>
                            <input
                                v-model="module.name"
                                type="text"
                                required
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1">
                            <span class="text-xs text-slate-500"
                                >Серийный номер</span
                            >
                            <input
                                v-model="module.serial_number"
                                type="text"
                                required
                                class="app-field"
                            />
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
