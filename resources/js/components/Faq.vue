<script>
export default {
    name: "Faq",
    data() {
        return {
            faqs: [],
            loading: true,
            activeId: null,
        };
    },
    mounted() {
        this.getFaqs();
    },
    methods: {
        async getFaqs() {
            try {
                const response = await axios.get("/api/faqs");
                this.faqs = response.data.map((faq) => ({
                    ...faq,
                    isOpen: false,
                }));
                this.loading = false;
            } catch (error) {
                console.log(error);
            }
        },
        toggleFaq(id) {
            // Закрываем все вкладки
            this.faqs.forEach((faq) => {
                faq.isOpen = false;
            });

            if (this.activeId === id) {
                this.activeId = null;
                return;
            }

            const faq = this.faqs.find((faq) => faq.id === id);
            if (faq) {
                faq.isOpen = true;
                this.activeId = id;
            }
        },

        isActive(id) {
            return this.activeId === id;
        },
    },
};
</script>

<template>
    <!-- Частые вопросы -->
    <div class="max-w-7xl mx-auto px-4 py-24">
        <div class="flex flex-col md:flex-row gap-8" v-if="!loading">
            <div class="flex-1 md:max-w-md">
                <div class="sticky top-24">
                    <h2
                        class="section-title text-3xl font-bold text-center mb-12"
                    >
                        Частые вопросы
                    </h2>
                    <p class="text-start text-lg">
                        Мы собрали самые частые вопросы, которые нам задают.
                        Если у вас остались вопросы, вы можете задать их
                        руководителю.
                    </p>
                    <div class="mt-12">
                        <a
                            href="https://t.me/+79832335907"
                            class="btn-primary inline-flex items-center hover:shadow-lg transition-all"
                        >
                            <i class="mdi mdi-telegram mr-2"></i>
                            Написать руководителю
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex-1 space-y-2">
                <div
                    class="faq-item"
                    style="border: none"
                    v-for="faq in faqs"
                    :key="faq.id"
                >
                    <div
                        class="faq-question transition-all hover:shadow-md border-0"
                        :class="{ 'is-open-faq': faq.isOpen }"
                        @click="toggleFaq(faq.id)"
                    >
                        <h3
                            class="flex items-center cursor-pointer font-medium"
                            style="margin-bottom: 0 !important"
                        >
                            <i
                                class="mdi mdi-help-circle-outline text-accent mr-3 text-xl"
                            ></i
                            >{{ faq.question }}
                        </h3>
                        <div class="faq-icon"></div>
                    </div>
                    <div
                        class="faq-answer border-0"
                        :class="{ 'is-open': faq.isOpen }"
                    >
                        <p v-html="faq.answer" class="text-gray-500"></p>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="loading">
            <div class="flex justify-center items-center h-full">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-accent"
                ></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.faq-question {
    padding: 1.25rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    margin-bottom: 0.25rem;
    position: relative;
    background-color: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.faq-question:hover {
    border-color: #d1d5db;
}

.is-open-faq {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom: none;
    background-color: #f9fafb;
}

.faq-icon {
    position: absolute;
    right: 1.25rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1.25rem;
    height: 1.25rem;
}

.faq-icon:before,
.faq-icon:after {
    content: "";
    position: absolute;
    background-color: #6b7280;
    transition: all 0.3s ease;
}

.faq-icon:before {
    top: 50%;
    left: 0;
    width: 100%;
    height: 2px;
    transform: translateY(-50%);
}

.faq-icon:after {
    top: 0;
    left: 50%;
    width: 2px;
    height: 100%;
    transform: translateX(-50%);
}

.is-open-faq .faq-icon:after {
    transform: translateX(-50%) rotate(90deg);
    opacity: 0;
}

.is-open-faq .faq-icon:before {
    background-color: #4f46e5; /* Accent color */
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: all 0.4s ease;
    opacity: 0;
}

.is-open {
    display: block;
    max-height: 1000px;
    padding: 1.25rem;
    border-left: 1px solid #e5e7eb;
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    border-radius: 0 0 0.5rem 0.5rem;
    margin-top: 0;
    margin-bottom: 1rem;
    opacity: 1;
    background-color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}
</style>
