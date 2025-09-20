<script>
import RepairForm from "./RepairForm.vue";
import SharpeningForm from "./SharpeningForm.vue";

export default {
    name: "OrderForm",
    components: {
        SharpeningForm,
        RepairForm,
    },
    props: {
        initialServiceType: {
            type: String,
            default: null,
            validator: (value) =>
                !value || ["sharpening", "repair"].includes(value),
        },
    },
    data() {
        return {
            serviceType: this.initialServiceType || "sharpening",
        };
    },
    methods: {
        setServiceType(type) {
            this.serviceType = type;
        },
    },
};
</script>

<template>
    <div class="space-y-8">
        <!-- Переключатель типа услуги -->
        <div v-if="!initialServiceType" class="service-type-selector mb-12">
            <div class="grid grid-cols-2 gap-6">
                <button
                    @click="setServiceType('sharpening')"
                    :class="[
                        'flex items-center justify-center p-8 rounded-3xl border-2 transition-all duration-500 shadow-2xl hover:shadow-3xl hover:scale-105 transform',
                        serviceType === 'sharpening'
                            ? 'border-blue-500 bg-blue-50/80 backdrop-blur-lg text-blue-600 dark:bg-blue-900/30 dark:border-blue-800/20 dark:text-blue-300'
                            : 'border-gray-200 dark:border-gray-700 text-dark-gray-700 dark:text-dark-gray-400 hover:border-blue-500/50 bg-white/60 backdrop-blur-md hover:bg-white/80 dark:bg-gray-800/60 dark:hover:bg-gray-700/80',
                    ]"
                >
                    <div class="text-center">
                        <div class="font-jost-bold text-xl">Заточка</div>
                        <div class="text-sm opacity-75 font-jost-regular">
                            Инструментов
                        </div>
                    </div>
                </button>

                <button
                    @click="setServiceType('repair')"
                    :class="[
                        'flex items-center justify-center p-8 rounded-3xl border-2 transition-all duration-500 shadow-2xl hover:shadow-3xl hover:scale-105 transform',
                        serviceType === 'repair'
                            ? 'border-pink-500 bg-pink-50/80 backdrop-blur-lg text-pink-600 dark:bg-pink-900/30 dark:border-pink-800/20 dark:text-pink-300'
                            : 'border-gray-200 text-dark-gray-700 dark:text-dark-gray-400 dark:border-gray-700 hover:border-pink-500/50 bg-white/60 backdrop-blur-md hover:bg-white/80 dark:bg-gray-800/60 dark:hover:bg-gray-700/80',
                    ]"
                >
                    <div class="text-center">
                        <div class="font-jost-bold text-xl">Ремонт</div>
                        <div class="text-sm opacity-75 font-jost-regular">
                            Оборудования
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Формы -->
        <SharpeningForm v-if="serviceType === 'sharpening'" />
        <RepairForm v-if="serviceType === 'repair'" />
    </div>
</template>
