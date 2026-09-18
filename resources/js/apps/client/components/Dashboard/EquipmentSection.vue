<script>
import { equipmentService } from "../../services/EquipmentService.js";

export default {
    name: "EquipmentSection",
    data() {
        return {
            items: [],
            loading: false,
            error: null,
            selectedId: null,
        };
    },
    computed: {
        selected() {
            return this.items.find((row) => row.id === this.selectedId) || null;
        },
    },
    mounted() {
        this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.items = await equipmentService.list();
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    "Не удалось загрузить оборудование";
            } finally {
                this.loading = false;
            }
        },
        open(item) {
            this.selectedId = item.id;
        },
        back() {
            this.selectedId = null;
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <div
            class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
        >
            <h2
                class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[90%] px-4 sm:px-6 bg-white dark:bg-dark-blue-500 text-lg sm:text-xl font-jost-bold text-[#C20A6C] text-center whitespace-nowrap"
            >
                ОБОРУДОВАНИЕ
            </h2>

            <div v-if="loading" class="mt-4 text-center py-12">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#C20A6C] mx-auto mb-4"
                ></div>
                <p class="text-gray-600 dark:text-gray-400">Загрузка...</p>
            </div>

            <p v-else-if="error" class="mt-4 text-center text-red-600">
                {{ error }}
            </p>

            <template v-else-if="selected">
                <button
                    type="button"
                    class="mb-4 text-[#C3006B] hover:underline"
                    @click="back"
                >
                    ← К списку
                </button>
                <h3
                    class="text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                >
                    {{ selected.name }}
                </h3>
                <p class="mt-2 text-dark-gray-500 dark:text-gray-300">
                    {{ selected.brand }} · {{ selected.type }}
                </p>
                <div class="mt-6">
                    <p class="font-jost-medium mb-2">Модули</p>
                    <ul
                        v-if="selected.modules?.length"
                        class="space-y-2"
                    >
                        <li
                            v-for="module in selected.modules"
                            :key="module.id || module.serial_number"
                            class="border border-dark-blue-500/20 px-4 py-3"
                        >
                            <p class="font-jost-medium">{{ module.name }}</p>
                            <p class="text-sm text-dark-gray-400">
                                S/N: {{ module.serial_number }}
                            </p>
                        </li>
                    </ul>
                    <p v-else class="text-dark-gray-400">Модулей нет</p>
                </div>
            </template>

            <div
                v-else-if="items.length === 0"
                class="mt-4 text-center py-12"
            >
                <p class="text-dark-gray-500 dark:text-gray-200">
                    Записей пока нет. Оборудование добавляет менеджер при
                    приёме.
                </p>
            </div>

            <div v-else class="mt-4 space-y-4">
                <button
                    v-for="item in items"
                    :key="item.id"
                    type="button"
                    class="w-full text-left border border-dark-blue-500/30 px-6 py-5 bg-white/60 dark:bg-gray-800/60 hover:shadow-lg transition-all"
                    @click="open(item)"
                >
                    <p
                        class="text-lg font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                    >
                        {{ item.name }}
                    </p>
                    <p class="mt-1 text-dark-gray-500 dark:text-gray-300">
                        {{ item.brand }} · {{ item.type }}
                    </p>
                    <p class="mt-1 text-sm text-dark-gray-400">
                        Модулей: {{ (item.modules || []).length }}
                    </p>
                </button>
            </div>
        </div>
    </div>
</template>
