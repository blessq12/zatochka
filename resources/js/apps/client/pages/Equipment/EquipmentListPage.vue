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
    <div class="space-y-5 lg:space-y-6">
        <ClientSectionCard title="ОБОРУДОВАНИЕ">
            <p
                v-if="loading"
                class="text-base text-dark-gray-500 dark:text-gray-300 lg:text-base"
            >
                Загрузка…
            </p>
            <p v-if="error" class="text-base text-red-600 lg:text-base">
                {{ error }}
            </p>
            <p
                v-else-if="!loading && items.length === 0"
                class="text-base text-dark-gray-500 dark:text-gray-300 lg:text-base"
            >
                Записей пока нет. Оборудование добавляет менеджер при приёме.
            </p>

            <div v-else class="space-y-3 sm:space-y-4">
                <button
                    v-for="item in items"
                    :key="item.id"
                    type="button"
                    class="w-full border border-dark-blue-500/20 bg-white/60 p-3 text-left backdrop-blur-md transition hover:border-[#C20A6C]/40 dark:border-gray-700/40 dark:bg-gray-800/60 sm:p-4"
                    @click="open(item)"
                >
                    <p
                        class="font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 lg:text-lg"
                    >
                        {{ item.name }}
                    </p>
                    <p
                        class="mt-1 text-base text-dark-gray-500 dark:text-gray-300 lg:mt-1 lg:text-base"
                    >
                        {{ item.brand }} · {{ item.type }}
                    </p>
                    <p
                        class="mt-1 text-base text-dark-gray-500 dark:text-gray-400 lg:mt-1 lg:text-base"
                    >
                        Модулей: {{ (item.modules || []).length }}
                    </p>
                </button>
            </div>
        </ClientSectionCard>
    </div>
</template>
