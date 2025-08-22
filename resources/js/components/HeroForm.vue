<script>
export default {
    name: "HeroForm",
    props: {
        header: {
            type: String,
            required: true,
        },
        description: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            currentStep: 1,
            formData: {
                toolType: "",
                subToolType: "",
                quantity: 1,
                serviceType: "sharpening", // 'sharpening' или 'repair'
                name: "",
                phone: "",
                needDelivery: false,
            },
            toolTypes: [
                {
                    id: "manicure",
                    name: "Маникюр и подология",
                    icon: "mdi-nail",
                    subTypes: ["ножницы", "кусачки", "твизеры", "пушеры"],
                },
                {
                    id: "haircut",
                    name: "Парикмахеры/барберы",
                    icon: "mdi-content-cut",
                    subTypes: [
                        "прямые ножницы",
                        "конвекс",
                        "филировочные",
                        "машинки",
                    ],
                },
                {
                    id: "groomer",
                    name: "Грумеры",
                    icon: "mdi-dog",
                    subTypes: ["ножницы", "машинки для стрижки шерсти"],
                },
                {
                    id: "lashes",
                    name: "Лешмейкеры/бровисты",
                    icon: "mdi-eye-outline",
                    subTypes: ["пинцеты"],
                },
                {
                    id: "repair",
                    name: "Ремонт оборудования",
                    icon: "mdi-tools",
                    subTypes: ["маникюрное", "педикюрное", "парикмахерское"],
                },
            ],
        };
    },
    computed: {
        selectedToolType() {
            return (
                this.toolTypes.find(
                    (type) => type.id === this.formData.toolType
                ) || null
            );
        },
        canGoNext() {
            if (this.currentStep === 1) {
                return !!this.formData.toolType && !!this.formData.subToolType;
            } else if (this.currentStep === 2) {
                return this.formData.quantity > 0;
            } else if (this.currentStep === 3) {
                return !!this.formData.name && !!this.formData.phone;
            }
            return true;
        },
    },
    methods: {
        selectToolType(typeId) {
            this.formData.toolType = typeId;
            this.formData.subToolType = "";
        },
        selectSubType(subType) {
            this.formData.subToolType = subType;
        },
        nextStep() {
            if (this.canGoNext && this.currentStep < 3) {
                this.currentStep++;
            }
        },
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        submitForm() {
            // Здесь будет логика отправки формы
            console.log("Отправка формы:", this.formData);
            // Можно добавить вызов API или другую логику
        },
    },
};
</script>

<template>
    <div
        class="rounded-2xl shadow-lg p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
    >
        <form class="space-y-4" @submit.prevent="submitForm">
            <!-- Шаги формы -->
            <div class="flex justify-between mb-4">
                <div
                    v-for="step in 3"
                    :key="step"
                    class="flex items-center"
                    :class="{
                        'text-accent font-bold': currentStep === step,
                        'text-gray-400': currentStep !== step,
                    }"
                >
                    <div
                        class="w-6 h-6 rounded-full flex items-center justify-center mr-2"
                        :class="{
                            'bg-accent text-white': currentStep >= step,
                            'bg-gray-200 dark:bg-gray-600': currentStep < step,
                        }"
                    >
                        {{ step }}
                    </div>
                    <span class="text-sm hidden sm:inline">
                        {{
                            step === 1
                                ? "Выбор инструмента"
                                : step === 2
                                ? "Количество и услуга"
                                : "Контактные данные"
                        }}
                    </span>
                </div>
            </div>

            <div v-if="currentStep === 1" class="animate-fade-up">
                <h3 class="text-lg font-bold mb-3 dark:text-white">
                    Выберите тип инструмента:
                </h3>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 sm:grid-cols-3 gap-3"
                >
                    <button
                        v-for="type in toolTypes"
                        :key="type.id"
                        type="button"
                        class="p-3 border-2 rounded-lg text-center hover:bg-accent/5 transition-all dark:text-white"
                        :class="
                            formData.toolType === type.id
                                ? 'border-accent bg-accent/5'
                                : 'border-gray-200 dark:border-gray-600'
                        "
                        @click="selectToolType(type.id)"
                    >
                        <div class="flex justify-center mb-2">
                            <i
                                :class="[
                                    'mdi',
                                    type.icon,
                                    'text-2xl',
                                    formData.toolType === type.id
                                        ? 'text-accent'
                                        : 'text-gray-500',
                                ]"
                            ></i>
                        </div>
                        <h4 class="font-bold text-sm dark:text-white">
                            {{ type.name }}
                        </h4>
                    </button>
                </div>

                <div v-if="selectedToolType" class="mt-4">
                    <h3 class="text-lg font-bold mb-3 dark:text-white">
                        Выберите подтип:
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <button
                            v-for="subType in selectedToolType.subTypes"
                            :key="subType"
                            type="button"
                            class="p-2 border-2 rounded-lg text-center hover:bg-accent/5 transition-all dark:text-white"
                            :class="
                                formData.subToolType === subType
                                    ? 'border-accent bg-accent/5'
                                    : 'border-gray-200 dark:border-gray-600'
                            "
                            @click="selectSubType(subType)"
                        >
                            {{ subType }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Шаг 2: Количество и тип услуги -->
            <div v-if="currentStep === 2" class="animate-fade-up">
                <h3 class="text-lg font-bold mb-3 dark:text-white">
                    Укажите детали:
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label"
                            >Количество инструментов</label
                        >
                        <div class="relative">
                            <input
                                v-model.number="formData.quantity"
                                type="number"
                                min="1"
                                class="form-input pl-10"
                                placeholder="Например, 3"
                            />
                            <i
                                class="mdi mdi-numeric absolute left-3 top-1/2 transform -translate-y-1/2 text-accent"
                            ></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Тип обращения</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <button
                                type="button"
                                class="p-2 border-2 rounded-lg text-center hover:bg-accent/5 transition-all dark:text-white"
                                :class="
                                    formData.serviceType === 'sharpening'
                                        ? 'border-accent bg-accent/5'
                                        : 'border-gray-200 dark:border-gray-600'
                                "
                                @click="formData.serviceType = 'sharpening'"
                            >
                                <i
                                    class="mdi mdi-scissors-cutting mb-1 text-xl"
                                ></i>
                                <div>Заточка</div>
                            </button>
                            <button
                                type="button"
                                class="p-2 border-2 rounded-lg text-center hover:bg-accent/5 transition-all dark:text-white"
                                :class="
                                    formData.serviceType === 'repair'
                                        ? 'border-accent bg-accent/5'
                                        : 'border-gray-200 dark:border-gray-600'
                                "
                                @click="formData.serviceType = 'repair'"
                            >
                                <i class="mdi mdi-tools mb-1 text-xl"></i>
                                <div>Ремонт</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Шаг 3: Контактные данные -->
            <div v-if="currentStep === 3" class="animate-fade-up">
                <h3 class="text-lg font-bold mb-3 dark:text-white">
                    Контактная информация:
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Ваше имя</label>
                        <div class="relative">
                            <input
                                v-model="formData.name"
                                type="text"
                                class="form-input pl-10"
                                required
                            />
                            <i
                                class="mdi mdi-account absolute left-3 top-1/2 transform -translate-y-1/2 text-accent"
                            ></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Телефон</label>
                        <div class="relative">
                            <input
                                v-model="formData.phone"
                                type="tel"
                                class="form-input pl-10"
                                required
                            />
                            <i
                                class="mdi mdi-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-accent"
                            ></i>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center gap-2 mb-2">
                        <input
                            v-model="formData.needDelivery"
                            type="checkbox"
                            id="needDelivery"
                            class="w-5 h-5 accent-accent"
                        />
                        <label for="needDelivery" class="dark:text-gray-300"
                            >Нужна доставка</label
                        >
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Бесплатная доставка: от 6 маникюрных или от 3
                        парикмахерских/грумерских инструментов. В остальных
                        случаях - 150 ₽ в одну сторону.
                    </p>
                </div>
            </div>

            <!-- Кнопки навигации -->
            <div class="flex justify-between mt-6 space-x-4">
                <button
                    v-if="currentStep > 1"
                    type="button"
                    class="btn-outline py-2 px-2 md:px-4 w-auto"
                    @click="prevStep"
                >
                    <i class="mdi mdi-arrow-left mr-1"></i>
                    <span class="hidden md:inline">Назад</span>
                </button>
                <div v-else></div>

                <button
                    v-if="currentStep < 3"
                    type="button"
                    class="btn-primary py-2 px-2 md:px-4 w-full md:w-auto"
                    :disabled="!canGoNext"
                    @click="nextStep"
                >
                    Далее
                    <i class="mdi mdi-arrow-right ml-1"></i>
                </button>
                <button
                    v-else
                    type="submit"
                    class="btn-primary py-2 px-4 w-full md:w-auto"
                    :disabled="!canGoNext"
                >
                    <i class="mdi mdi-send mr-1"></i>
                    Отправить
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.animate-fade-up {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
