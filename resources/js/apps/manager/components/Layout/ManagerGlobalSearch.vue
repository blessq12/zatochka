<script>
import { searchService } from "../../services/SearchService.js";

export default {
    name: "ManagerGlobalSearch",
    data() {
        return {
            query: "",
            clients: [],
            equipments: [],
            open: false,
            loading: false,
            timer: null,
            error: null,
        };
    },
    computed: {
        hasResults() {
            return this.clients.length > 0 || this.equipments.length > 0;
        },
        showPanel() {
            return this.open && String(this.query).trim().length >= 2;
        },
    },
    beforeUnmount() {
        clearTimeout(this.timer);
        document.removeEventListener("click", this.onDocumentClick);
    },
    mounted() {
        document.addEventListener("click", this.onDocumentClick);
    },
    methods: {
        onDocumentClick(event) {
            if (!this.$refs.root?.contains(event.target)) {
                this.open = false;
            }
        },
        onInput() {
            clearTimeout(this.timer);
            this.error = null;
            const q = String(this.query || "").trim();
            if (q.length < 2) {
                this.clients = [];
                this.equipments = [];
                this.loading = false;
                return;
            }
            this.loading = true;
            this.open = true;
            this.timer = setTimeout(() => this.runSearch(q), 300);
        },
        async runSearch(q) {
            try {
                const data = await searchService.search(q);
                if (String(this.query || "").trim() !== q) {
                    return;
                }
                this.clients = data.clients;
                this.equipments = data.equipments;
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось выполнить поиск";
                this.clients = [];
                this.equipments = [];
            } finally {
                this.loading = false;
            }
        },
        onFocus() {
            if (String(this.query || "").trim().length >= 2) {
                this.open = true;
            }
        },
        onKeydown(event) {
            if (event.key === "Escape") {
                this.open = false;
            }
        },
        matchedSerial(equipment) {
            const q = String(this.query || "").trim().toLowerCase();
            if (!q) {
                return null;
            }
            for (const module of equipment.modules || []) {
                const serial = String(module.serial_number || "").toLowerCase();
                if (serial.includes(q)) {
                    return module.serial_number;
                }
            }
            return null;
        },
        goClient(client) {
            this.open = false;
            this.query = "";
            this.clients = [];
            this.equipments = [];
            this.$router.push({
                name: "manager.users.edit",
                params: { type: "clients", id: String(client.id) },
            });
        },
        goEquipment(equipment) {
            this.open = false;
            this.query = "";
            this.clients = [];
            this.equipments = [];
            this.$router.push({
                name: "manager.equipment.edit",
                params: { id: String(equipment.id) },
            });
        },
    },
};
</script>

<template>
    <div ref="root" class="relative w-full">
        <input
            v-model="query"
            type="search"
            class="app-field !min-h-9 !py-1.5 !text-sm"
            placeholder="Клиент, телефон, оборудование…"
            autocomplete="off"
            @input="onInput"
            @focus="onFocus"
            @keydown="onKeydown"
        />

        <div
            v-if="showPanel"
            class="absolute left-0 right-0 z-50 mt-1 max-h-[min(24rem,70vh)] overflow-y-auto border border-slate-200 bg-white shadow-lg"
        >
            <p v-if="loading" class="px-3 py-2 text-xs text-slate-500">
                Поиск…
            </p>
            <p v-else-if="error" class="px-3 py-2 text-xs text-red-600">
                {{ error }}
            </p>
            <template v-else-if="hasResults">
                <div v-if="clients.length" class="border-b border-slate-100 py-1">
                    <p
                        class="px-3 py-1 text-[11px] font-jost-medium uppercase tracking-wide text-slate-500"
                    >
                        Клиенты
                    </p>
                    <button
                        v-for="client in clients"
                        :key="`c-${client.id}`"
                        type="button"
                        class="flex w-full flex-col gap-0.5 px-3 py-2 text-left hover:bg-slate-50"
                        @click="goClient(client)"
                    >
                        <span class="text-sm text-dark-blue-500">
                            {{ client.name || client.email || `#${client.id}` }}
                        </span>
                        <span class="text-xs text-slate-500">
                            {{ client.phone || "без телефона" }}
                        </span>
                    </button>
                </div>
                <div v-if="equipments.length" class="py-1">
                    <p
                        class="px-3 py-1 text-[11px] font-jost-medium uppercase tracking-wide text-slate-500"
                    >
                        Оборудование
                    </p>
                    <button
                        v-for="equipment in equipments"
                        :key="`e-${equipment.id}`"
                        type="button"
                        class="flex w-full flex-col gap-0.5 px-3 py-2 text-left hover:bg-slate-50"
                        @click="goEquipment(equipment)"
                    >
                        <span class="text-sm text-dark-blue-500">
                            {{ equipment.name }}
                            <span
                                v-if="equipment.brand"
                                class="text-slate-500"
                            >
                                · {{ equipment.brand }}
                            </span>
                        </span>
                        <span class="text-xs text-slate-500">
                            <template v-if="matchedSerial(equipment)">
                                S/N {{ matchedSerial(equipment) }}
                                ·
                            </template>
                            {{ equipment.client_name || `клиент #${equipment.client_id}` }}
                        </span>
                    </button>
                </div>
            </template>
            <p v-else class="px-3 py-2 text-xs text-slate-500">
                Ничего не найдено
            </p>
        </div>
    </div>
</template>
