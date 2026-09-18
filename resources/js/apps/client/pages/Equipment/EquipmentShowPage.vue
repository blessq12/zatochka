<script>
import ClientSectionCard from "../../components/Layout/ClientSectionCard.vue";
import { equipmentService } from "../../services/EquipmentService.js";

export default {
    name: "ClientEquipmentShowPage",
    components: { ClientSectionCard },
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
    },
};
</script>

<template>
    <div class="space-y-4">
        <p v-if="loading" class="text-base text-dark-gray-500">
            Загрузка…
        </p>
        <p v-if="error" class="text-base text-red-600 lg:text-base">{{ error }}</p>

        <ClientSectionCard v-if="item && !loading" title="КАРТОЧКА">
            <dl class="mb-4 space-y-2 text-base lg:mb-6 lg:space-y-3 lg:text-base">
                <div class="flex justify-between gap-2">
                    <dt class="text-dark-gray-500 dark:text-gray-400">Бренд</dt>
                    <dd class="text-dark-gray-500 dark:text-gray-200">
                        {{ item.brand }}
                    </dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-dark-gray-500 dark:text-gray-400">Тип</dt>
                    <dd class="text-dark-gray-500 dark:text-gray-200">
                        {{ item.type }}
                    </dd>
                </div>
            </dl>

            <h3
                class="mb-2 text-base font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 lg:mb-4 lg:text-base"
            >
                Модули
            </h3>
            <ul
                v-if="item.modules?.length"
                class="divide-y divide-dark-blue-500/10 dark:divide-white/10 lg:space-y-3 lg:divide-y-0"
            >
                <li
                    v-for="module in item.modules"
                    :key="module.id || module.serial_number"
                    class="py-3.5 first:pt-0 last:pb-0 lg:border lg:border-white/20 lg:bg-white/60 lg:p-4 lg:backdrop-blur-md dark:lg:border-gray-700/20 dark:lg:bg-gray-800/60"
                >
                    <p
                        class="text-base font-jost-medium text-dark-blue-500 dark:text-dark-blue-300 lg:text-base"
                    >
                        {{ module.name }}
                    </p>
                    <p class="mt-1 text-base text-dark-gray-500 dark:text-gray-300">
                        S/N: {{ module.serial_number }}
                    </p>
                </li>
            </ul>
            <p
                v-else
                class="text-base text-dark-gray-500 dark:text-gray-400 lg:text-base"
            >
                Модулей нет
            </p>
        </ClientSectionCard>
    </div>
</template>
