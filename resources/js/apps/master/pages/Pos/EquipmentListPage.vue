<script>
import { equipmentService } from "../../services/EquipmentService.js";

export default {
    name: "EquipmentListPage",
    data() {
        return {
            items: [],
            loading: false,
            error: null,
        };
    },
    async mounted() {
        await this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.items = await equipmentService.list();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить список";
                this.items = [];
            } finally {
                this.loading = false;
            }
        },
        modulesLabel(item) {
            const modules = item.modules || [];
            if (modules.length === 0) {
                return "—";
            }
            return modules
                .map((m) => `${m.name} (${m.serial_number})`)
                .join(", ");
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-jost-bold text-dark-blue-500">Оборудование</h1>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <div v-if="!loading" class="overflow-x-auto border border-slate-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-jost-medium">Название</th>
                        <th class="px-4 py-3 font-jost-medium">Бренд</th>
                        <th class="px-4 py-3 font-jost-medium">Тип</th>
                        <th class="px-4 py-3 font-jost-medium">Модули</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in items"
                        :key="item.id"
                        class="border-t border-slate-100"
                    >
                        <td class="px-4 py-3">{{ item.name }}</td>
                        <td class="px-4 py-3">{{ item.brand }}</td>
                        <td class="px-4 py-3">{{ item.type }}</td>
                        <td class="px-4 py-3">{{ modulesLabel(item) }}</td>
                    </tr>
                    <tr v-if="items.length === 0">
                        <td
                            colspan="4"
                            class="px-4 py-6 text-center text-slate-500"
                        >
                            Пока пусто
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
