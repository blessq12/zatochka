<script>
import { equipmentService } from "../../services/EquipmentService.js";

export default {
    name: "ClientEquipmentShowPage",
    data() {
        return {
            item: null,
            loading: false,
            error: null,
        };
    },
    mounted() {
        this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.item = await equipmentService.get(this.$route.params.id);
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    "Не удалось загрузить оборудование";
            } finally {
                this.loading = false;
            }
        },
        back() {
            this.$router.push({ name: "client.equipment" });
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">
                {{ item?.name || `Оборудование #${$route.params.id}` }}
            </h1>
            <button type="button" class="app-btn-ghost w-full sm:w-auto" @click="back">
                К списку
            </button>
        </div>

        <p v-if="loading" class="text-base text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-base text-red-600">{{ error }}</p>

        <section v-if="item && !loading" class="app-panel space-y-3">
            <dl class="space-y-2 text-base">
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500">Бренд</dt>
                    <dd>{{ item.brand }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500">Тип</dt>
                    <dd>{{ item.type }}</dd>
                </div>
            </dl>

            <div>
                <h2 class="text-base font-jost-bold text-dark-blue-500">Модули</h2>
                <ul
                    v-if="item.modules?.length"
                    class="mt-2 space-y-2"
                >
                    <li
                        v-for="module in item.modules"
                        :key="module.id || module.serial_number"
                        class="border border-slate-200 p-3 text-base"
                    >
                        <p class="font-jost-medium">{{ module.name }}</p>
                        <p class="text-slate-600">
                            S/N: {{ module.serial_number }}
                        </p>
                    </li>
                </ul>
                <p v-else class="mt-2 text-base text-slate-500">Модулей нет</p>
            </div>
        </section>
    </div>
</template>
