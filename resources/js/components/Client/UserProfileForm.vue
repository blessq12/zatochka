<script>
import { gsap } from "gsap";
import { mapStores } from "pinia";
import * as yup from "yup";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "UserProfileForm",
    props: {
        open: { type: Boolean, default: false },
    },
    emits: ["close"],
    data() {
        return {
            form: {
                full_name: "",
                email: "",
                telegram: "",
                birth_date: "",
                delivery_address: "",
            },
            errors: {},
        };
    },
    computed: {
        ...mapStores(useAuthStore),
    },
    watch: {
        open(newVal) {
            if (newVal) {
                this.populateFormFromStore();
            }
        },
    },
    methods: {
        async validateForm() {
            const schema = yup.object({
                full_name: yup.string().required("Укажите имя"),
                email: yup
                    .string()
                    .nullable()
                    .transform((v) => (v === "" ? null : v))
                    .email("Некорректный email"),
                telegram: yup.string().nullable(),
                birth_date: yup.string().nullable(),
                delivery_address: yup.string().nullable(),
            });

            try {
                this.errors = {};
                await schema.validate(this.form, { abortEarly: false });
                return { valid: true };
            } catch (e) {
                const fieldErrors = {};
                if (e.inner?.length) {
                    e.inner.forEach((err) => {
                        if (!fieldErrors[err.path])
                            fieldErrors[err.path] = err.message;
                    });
                } else if (e.path) {
                    fieldErrors[e.path] = e.message;
                }
                this.errors = fieldErrors;
                return { valid: false };
            }
        },
        // GSAP hooks for overlay fade
        onOverlayEnter(el, done) {
            gsap.fromTo(
                el,
                { opacity: 0 },
                {
                    opacity: 1,
                    duration: 0.35,
                    ease: "power2.out",
                    onComplete: done,
                }
            );
        },
        onOverlayLeave(el, done) {
            gsap.to(el, {
                opacity: 0,
                duration: 0.3,
                delay: 0.35,
                ease: "power2.in",
                onComplete: done,
            });
        },
        // GSAP hooks for panel slide
        onPanelEnter(el, done) {
            gsap.fromTo(
                el,
                { x: "100%" },
                {
                    x: "0%",
                    duration: 0.5,
                    delay: 0.35,
                    ease: "power3.out",
                    onComplete: () => {
                        gsap.set(el, { clearProps: "transform" });
                        done();
                    },
                }
            );
        },
        onPanelLeave(el, done) {
            gsap.to(el, {
                x: "100%",
                duration: 0.4,
                ease: "power3.in",
                onComplete: done,
            });
        },
        async onSave() {
            const { valid } = await this.validateForm();
            if (!valid) return;

            const result = await this.authStore.updateClient(this.form);
            if (result.success) {
                this.$emit("close");
            }
        },
        onCancel() {
            this.$emit("close");
        },
        onBackdrop() {
            // по требованиям: закрываем только по явной отмене, но допустим клик по фону не закрывает
            // оставим без действия
        },
        populateFormFromStore() {
            const u = this.authStore?.user || {};
            this.form.full_name = u.full_name || "";
            this.form.email = u.email || "";
            this.form.telegram = u.telegram || "";
            this.form.birth_date = this.formatDateForInput(u.birth_date) || "";
            this.form.delivery_address = u.delivery_address || "";
        },
        formatDateForInput(date) {
            if (!date) return "";

            // Если дата уже в формате YYYY-MM-DD, возвращаем как есть
            if (typeof date === "string" && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
                return date;
            }

            // Если это timestamp, парсим и форматируем для input[type="date"]
            try {
                const dateObj = new Date(date);
                return dateObj.toISOString().split("T")[0];
            } catch (error) {
                console.error("Error formatting date for input:", error);
                return date;
            }
        },
    },
};
</script>

<template>
    <div class="absolute inset-0 z-50 flex pointer-events-none">
        <transition
            :css="false"
            appear
            @enter="onOverlayEnter"
            @leave="onOverlayLeave"
        >
            <div
                v-if="open"
                class="absolute inset-0 bg-black/30 z-0 pointer-events-auto"
                @click="onBackdrop"
            />
        </transition>
        <transition
            :css="false"
            appear
            @enter="onPanelEnter"
            @leave="onPanelLeave"
        >
            <aside
                v-if="open"
                class="w-full sm:w-[480px] h-full min-h-0 bg-white/85 dark:bg-dark-blue-500/85 backdrop-blur-2xl border-l border-white/25 dark:border-gray-800/25 shadow-2xl p-8 sm:p-10 lg:p-12 text-gray-900 dark:text-gray-100 flex flex-col overflow-y-auto z-10 ml-auto pointer-events-auto"
            >
                <header class="mb-10 sm:mb-12">
                    <h2
                        class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100"
                    >
                        Редактировать профиль
                    </h2>
                    <p class="text-gray-700 dark:text-gray-300 mt-2">
                        Обновите личные данные
                    </p>
                </header>

                <div class="space-y-6 flex-1">
                    <div>
                        <label
                            class="block text-lg font-medium text-gray-700 dark:text-gray-300 mb-3"
                            >Имя</label
                        >
                        <input
                            v-model="form.full_name"
                            class="w-full px-6 py-4 bg-white/60 dark:bg-gray-800/60 backdrop-blur-md border border-white/20 dark:border-gray-700/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300"
                            placeholder="Иван Иванов"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-lg font-medium text-gray-700 dark:text-gray-300 mb-3"
                            >Email</label
                        >
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full px-6 py-4 bg-white/60 dark:bg-gray-800/60 backdrop-blur-md border border-white/20 dark:border-gray-700/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300"
                            placeholder="user@example.com"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-lg font-medium text-gray-700 dark:text-gray-300 mb-3"
                            >Telegram</label
                        >
                        <input
                            v-model="form.telegram"
                            class="w-full px-6 py-4 bg-white/60 dark:bg-gray-800/60 backdrop-blur-md border border-white/20 dark:border-gray-700/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300"
                            placeholder="@username"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-lg font-medium text-gray-700 dark:text-gray-300 mb-3"
                            >Дата рождения</label
                        >
                        <input
                            v-model="form.birth_date"
                            type="date"
                            class="w-full px-6 py-4 bg-white/60 dark:bg-gray-800/60 backdrop-blur-md border border-white/20 dark:border-gray-700/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-lg font-medium text-gray-700 dark:text-gray-300 mb-3"
                            >Адрес доставки</label
                        >
                        <textarea
                            v-model="form.delivery_address"
                            rows="3"
                            class="w-full px-6 py-4 bg-white/60 dark:bg-gray-800/60 backdrop-blur-md border border-white/20 dark:border-gray-700/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300"
                            placeholder="Город, улица, дом, квартира"
                        ></textarea>
                    </div>
                </div>

                <footer class="mt-12 flex gap-4">
                    <button
                        @click="onSave"
                        class="bg-blue-600/90 backdrop-blur-xs hover:bg-blue-700/90 text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-blue-500/90 dark:hover:bg-blue-600/90"
                    >
                        Сохранить
                    </button>
                    <button
                        @click="onCancel"
                        class="bg-white/60 backdrop-blur-xs hover:bg-white/80 text-gray-900 px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl border border-white/20 dark:bg-gray-800/60 dark:hover:bg-gray-700/80 dark:text-gray-100 dark:border-gray-700/20"
                    >
                        Отмена
                    </button>
                </footer>
            </aside>
        </transition>
    </div>
</template>

<style scoped></style>
