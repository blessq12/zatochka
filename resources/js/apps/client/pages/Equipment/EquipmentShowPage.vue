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
        back() {
            this.$router.push({ name: "client.equipment" });
        },
    },
};
</script>

<template>
    <div class="space-y-3 lg:space-y-6">
        <div class="flex items-center justify-end lg:justify-between">
            <h2
                class="hidden font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 lg:block lg:text-2xl"
            >
                {{ item?.name || `Оборудование #${$route.params.id}` }}
            </h2>
            <button
                type="button"
                class="text-sm font-jost-medium text-dark-gray-500 hover:text-[#C3006B] dark:text-gray-200 lg:border lg:border-dark-blue-500/30 lg:px-4 lg:py-3 lg:hover:bg-white/60"
                @click="back"
            >
                ← К списку
            </button>
        </div>

        <p v-if="loading" class="text-sm text-dark-gray-500 lg:text-base">
            Загрузка…
        </p>
        <p v-if="error" class="text-sm text-red-600 lg:text-base">{{ error }}</p>

        <ClientSectionCard v-if="item && !loading" title="КАРТОЧКА">
            <dl class="mb-3 space-y-2 text-sm lg:mb-6 lg:space-y-3 lg:text-base">
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
                class="mb-2 text-sm font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 lg:mb-3 lg:text-base"
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
                    class="py-2.5 first:pt-0 last:pb-0 lg:border lg:border-white/20 lg:bg-white/60 lg:p-4 lg:backdrop-blur-md dark:lg:border-gray-700/20 dark:lg:bg-gray-800/60"
                >
                    <p
                        class="text-sm font-jost-medium text-dark-blue-500 dark:text-dark-blue-300 lg:text-base"
                    >
                        {{ module.name }}
                    </p>
                    <p class="mt-0.5 text-sm text-dark-gray-500 dark:text-gray-300">
                        S/N: {{ module.serial_number }}
                    </p>
                </li>
            </ul>
            <p
                v-else
                class="text-sm text-dark-gray-500 dark:text-gray-400 lg:text-base"
            >
                Модулей нет
            </p>
        </ClientSectionCard>
    </div>
</template>
