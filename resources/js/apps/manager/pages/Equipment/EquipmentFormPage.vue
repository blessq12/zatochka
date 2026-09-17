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
                modules: [],
            },
        };
    },
    computed: {
        isEdit() {
            return !!this.$route.params.id;
        },
        title() {
            return this.isEdit
                ? "Редактирование оборудования"
                : "Новое оборудование";
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
        },
        async submit() {
            this.saving = true;
            this.error = null;
            try {
                const payload = {
                    name: this.form.name,
                    brand: this.form.brand,
                    type: this.form.type,
                    modules: this.form.modules.map((m) => ({
                        name: m.name,
                        serial_number: m.serial_number,
                    })),
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
    },
};
</script>

<template>
    <div class="mx-auto max-w-xl space-y-6">
        <h1 class="text-2xl font-jost-bold text-dark-blue-500">{{ title }}</h1>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <form v-if="!loading" class="space-y-4" @submit.prevent="submit">
            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Клиент</span>
                <select
                    v-model="form.client_id"
                    class="w-full border border-slate-300 px-3 py-2"
                    :disabled="isEdit"
                    required
                >
                    <option value="" disabled>Выберите клиента</option>
                    <option
                        v-for="client in clients"
                        :key="client.id"
                        :value="String(client.id)"
                    >
                        {{ client.name || client.email || `#${client.id}` }}
                    </option>
                </select>
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Название</span>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Бренд</span>
                <input
                    v-model="form.brand"
                    type="text"
                    required
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>

            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Тип</span>
                <input
                    v-model="form.type"
                    type="text"
                    required
                    class="w-full border border-slate-300 px-3 py-2"
                />
            </label>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600">Модули</span>
                    <button
                        type="button"
                        class="text-sm text-pink-600 hover:underline"
                        @click="addModule"
                    >
                        + модуль
                    </button>
                </div>

                <div
                    v-for="(module, index) in form.modules"
                    :key="index"
                    class="flex flex-col gap-2 border border-slate-200 p-3 sm:flex-row sm:items-end"
                >
                    <label class="block flex-1 space-y-1">
                        <span class="text-xs text-slate-500">Название</span>
                        <input
                            v-model="module.name"
                            type="text"
                            required
                            class="w-full border border-slate-300 px-3 py-2"
                        />
                    </label>
                    <label class="block flex-1 space-y-1">
                        <span class="text-xs text-slate-500">Серийный номер</span>
                        <input
                            v-model="module.serial_number"
                            type="text"
                            required
                            class="w-full border border-slate-300 px-3 py-2"
                        />
                    </label>
                    <button
                        type="button"
                        class="text-sm text-red-600 hover:underline sm:mb-2"
                        @click="removeModule(index)"
                    >
                        Убрать
                    </button>
                </div>

                <p
                    v-if="form.modules.length === 0"
                    class="text-xs text-slate-500"
                >
                    Можно без модулей
                </p>
            </div>

            <div class="flex gap-3 pt-2">
                <button
                    type="submit"
                    class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                    :disabled="saving"
                >
                    {{ saving ? "Сохранение…" : "Сохранить" }}
                </button>
                <button
                    type="button"
                    class="border border-slate-300 px-4 py-2 text-sm text-slate-600"
                    @click="cancel"
                >
                    Отмена
                </button>
            </div>
        </form>
    </div>
</template>
