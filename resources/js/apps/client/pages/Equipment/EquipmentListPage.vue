<script>
import ClientSectionCard from "../../components/Layout/ClientSectionCard.vue";
import { equipmentService } from "../../services/EquipmentService.js";

export default {
    name: "ClientEquipmentListPage",
    components: { ClientSectionCard },
    data() {
        return {
            items: [],
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
            this.$router.push({
                name: "client.equipment.show",
                params: { id: item.id },
            });
        },
    },
};
</script>

<template>
    <div class="space-y-3 lg:space-y-6">
        <ClientSectionCard title="ОБОРУДОВАНИЕ">
            <p
                v-if="loading"
                class="text-sm text-dark-gray-500 dark:text-gray-300 lg:text-base"
            >
                Загрузка…
            </p>
            <p v-if="error" class="text-sm text-red-600 lg:text-base">
                {{ error }}
            </p>
            <p
                v-else-if="!loading && items.length === 0"
                class="text-sm text-dark-gray-500 dark:text-gray-300 lg:text-base"
            >
                Записей пока нет. Оборудование добавляет менеджер при приёме.
            </p>

            <div
                v-else
                class="divide-y divide-dark-blue-500/10 dark:divide-white/10 lg:space-y-4 lg:divide-y-0"
            >
                <button
                    v-for="item in items"
                    :key="item.id"
                    type="button"
                    class="w-full py-3 text-left transition first:pt-0 last:pb-0 hover:bg-white/40 lg:border lg:border-dark-blue-500/20 lg:bg-white/60 lg:p-4 lg:backdrop-blur-md lg:first:pt-4 lg:hover:border-[#C3006B]/40 dark:lg:border-gray-700/40 dark:lg:bg-gray-800/60"
                    @click="open(item)"
                >
                    <p
                        class="font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 lg:text-lg"
                    >
                        {{ item.name }}
                    </p>
                    <p
                        class="mt-0.5 text-sm text-dark-gray-500 dark:text-gray-300 lg:mt-1 lg:text-base"
                    >
                        {{ item.brand }} · {{ item.type }}
                    </p>
                    <p
                        class="mt-0.5 text-xs text-dark-gray-500 dark:text-gray-400 lg:mt-1 lg:text-sm"
                    >
                        Модулей: {{ (item.modules || []).length }}
                    </p>
                </button>
            </div>
        </ClientSectionCard>
    </div>
</template>
