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
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2
                class="text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 sm:text-2xl"
            >
                {{ item?.name || `Оборудование #${$route.params.id}` }}
            </h2>
            <button
                type="button"
                class="w-full border border-dark-blue-500/30 px-4 py-3 font-jost-medium text-dark-gray-500 transition hover:bg-white/60 sm:w-auto dark:text-gray-200"
                @click="back"
            >
                К списку
            </button>
        </div>

        <p v-if="loading" class="text-base text-dark-gray-500">Загрузка…</p>
        <p v-if="error" class="text-base text-red-600">{{ error }}</p>

        <ClientSectionCard v-if="item && !loading" title="КАРТОЧКА">
            <dl class="mb-6 space-y-3 text-base">
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
                class="mb-3 text-base font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
            >
                Модули
            </h3>
            <ul v-if="item.modules?.length" class="space-y-3">
                <li
                    v-for="module in item.modules"
                    :key="module.id || module.serial_number"
                    class="border border-white/20 bg-white/60 p-4 backdrop-blur-md dark:border-gray-700/20 dark:bg-gray-800/60"
                >
                    <p class="font-jost-medium text-dark-blue-500 dark:text-dark-blue-300">
                        {{ module.name }}
                    </p>
                    <p class="mt-1 text-dark-gray-500 dark:text-gray-300">
                        S/N: {{ module.serial_number }}
                    </p>
                </li>
            </ul>
            <p v-else class="text-base text-dark-gray-500 dark:text-gray-400">
                Модулей нет
            </p>
        </ClientSectionCard>
    </div>
</template>
