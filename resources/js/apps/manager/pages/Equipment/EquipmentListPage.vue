<script>
import { equipmentService } from "../../services/EquipmentService.js";
import { actorService } from "../../services/ActorService.js";

export default {
    name: "EquipmentListPage",
    data() {
        return {
            items: [],
            clients: [],
            clientId: this.$route.query.client_id || "",
            loading: false,
            error: null,
        };
    },
    watch: {
        "$route.query.client_id"(value) {
            this.clientId = value || "";
            this.load();
        },
    },
    async mounted() {
        await this.loadClients();
        await this.load();
    },
    methods: {
        async loadClients() {
            try {
                this.clients = await actorService.list("clients");
            } catch {
                this.clients = [];
            }
        },
        selectClient(clientId) {
            const query = {};
            if (clientId) {
                query.client_id = String(clientId);
            }
            this.$router.replace({ name: "manager.equipment", query });
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.items = await equipmentService.list(this.clientId || null);
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить список";
                this.items = [];
            } finally {
                this.loading = false;
            }
        },
        goCreate() {
            this.$router.push({
                name: "manager.equipment.create",
                query: this.clientId ? { client_id: this.clientId } : {},
            });
        },
        goEdit(item) {
            this.$router.push({
                name: "manager.equipment.edit",
                params: { id: String(item.id) },
            });
        },
        async remove(item) {
            if (!confirm(`Удалить «${item.name}»?`)) {
                return;
            }
            try {
                await equipmentService.remove(item.id);
                await this.load();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось удалить";
            }
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-jost-bold text-dark-blue-500">
                Оборудование
            </h1>
            <button
                type="button"
                class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600"
                @click="goCreate"
            >
                Создать
            </button>
        </div>

        <label class="block max-w-sm space-y-1">
            <span class="text-sm text-slate-600">Клиент</span>
            <select
                :value="clientId"
                class="w-full border border-slate-300 px-3 py-2"
                @change="selectClient($event.target.value)"
            >
                <option value="">Все клиенты</option>
                <option
                    v-for="client in clients"
                    :key="client.id"
                    :value="String(client.id)"
                >
                    {{ client.name || client.email || `#${client.id}` }}
                </option>
            </select>
        </label>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <div v-if="!loading" class="overflow-x-auto border border-slate-200">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-3 py-2 font-jost-medium">Название</th>
                        <th class="px-3 py-2 font-jost-medium">Бренд</th>
                        <th class="px-3 py-2 font-jost-medium">Тип</th>
                        <th class="px-3 py-2 font-jost-medium">Клиент</th>
                        <th class="px-3 py-2 font-jost-medium">Модули</th>
                        <th class="px-3 py-2 font-jost-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in items"
                        :key="item.id"
                        class="border-t border-slate-100"
                    >
                        <td class="px-3 py-2">{{ item.name }}</td>
                        <td class="px-3 py-2">{{ item.brand }}</td>
                        <td class="px-3 py-2">{{ item.type }}</td>
                        <td class="px-3 py-2">
                            {{ item.client_name || `#${item.client_id}` }}
                        </td>
                        <td class="px-3 py-2">{{ item.modules?.length || 0 }}</td>
                        <td class="px-3 py-2 text-right space-x-2">
                            <button
                                type="button"
                                class="text-pink-600 hover:underline"
                                @click="goEdit(item)"
                            >
                                Изменить
                            </button>
                            <button
                                type="button"
                                class="text-red-600 hover:underline"
                                @click="remove(item)"
                            >
                                Удалить
                            </button>
                        </td>
                    </tr>
                    <tr v-if="items.length === 0">
                        <td
                            colspan="6"
                            class="px-3 py-6 text-center text-slate-500"
                        >
                            Пока пусто
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
