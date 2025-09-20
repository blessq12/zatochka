<template>
    <div class="max-w-7xl mx-auto">
        <!-- Search Section -->
        <div class="mb-12 search-section">
            <div class="relative max-w-2xl mx-auto">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Поиск по вопросам..."
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-lg dark:bg-gray-800/60 dark:border-gray-600/20 dark:text-gray-100"
                />
                <div
                    class="absolute right-4 top-1/2 transform -translate-y-1/2"
                >
                    <span class="text-2xl">🔍</span>
                </div>
            </div>
        </div>

        <!-- FAQ Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <div
                v-for="category in categories"
                :key="category.id"
                @click="selectCategory(category.id)"
                :data-category-id="category.id"
                :class="[
                    'category-card cursor-pointer bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 border border-white/25 hover:shadow-3xl hover:bg-white/95 hover:backdrop-blur-3xl transition-all duration-500 text-center dark:bg-gray-800/90 dark:border-gray-600/30 dark:hover:bg-gray-800/20',
                    selectedCategory === category.id
                        ? 'ring-2 ring-blue-500/50 bg-blue-50/80 dark:bg-blue-900/60 dark:ring-blue-400/50'
                        : '',
                ]"
            >
                <div
                    class="w-16 h-16 bg-blue-500/20 rounded-3xl flex items-center justify-center mx-auto mb-4 dark:bg-blue-500/20"
                >
                    <span class="text-3xl">{{ category.icon }}</span>
                </div>
                <h3
                    class="text-xl font-jost-bold text-dark-gray-500 mb-2 dark:text-gray-100"
                >
                    {{ category.title }}
                </h3>
                <p
                    class="text-lg font-jost-regular text-gray-500 dark:text-gray-200"
                >
                    {{ category.description }}
                </p>
                <div
                    class="mt-4 text-sm text-blue-600 dark:text-blue-300 font-medium"
                >
                    {{ category.count }} вопросов
                </div>
            </div>
        </div>

        <!-- FAQ Items -->
        <div class="space-y-6">
            <div
                v-for="(faq, index) in filteredFaqs"
                :key="faq.id"
                :data-faq-id="faq.id"
                class="faq-item bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl border border-white/25 hover:shadow-3xl transition-all duration-500 overflow-hidden dark:bg-gray-800/90 dark:border-gray-600/30"
            >
                <button
                    @click="toggleFaq(faq.id)"
                    class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-white/95 transition-all duration-300 dark:hover:bg-gray-800/20"
                >
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 bg-blue-500/20 rounded-2xl flex items-center justify-center mr-4 dark:bg-blue-500/20"
                        >
                            <span class="text-xl">{{ faq.icon }}</span>
                        </div>
                        <div>
                            <h3
                                class="text-xl font-jost-bold text-dark-gray-500 dark:text-gray-100 mb-1"
                            >
                                {{ faq.question }}
                            </h3>
                            <p
                                class="text-lg font-jost-regular text-gray-500 dark:text-gray-200"
                            >
                                {{ faq.category }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="faq-arrow text-2xl text-blue-500 transition-transform duration-300 dark:text-blue-300"
                        :class="{ 'rotate-180': faq.isOpen }"
                    >
                        ▼
                    </div>
                </button>

                <div
                    v-if="faq.isOpen"
                    class="faq-content px-8 pb-6 border-t border-gray-200/30 dark:border-gray-600/30"
                >
                    <div class="pt-6">
                        <div
                            class="prose prose-lg max-w-none text-gray-700 dark:text-gray-200"
                        >
                            <p class="text-lg leading-relaxed">
                                {{ faq.answer }}
                            </p>
                            <div
                                v-if="faq.steps && faq.steps.length > 0"
                                class="mt-6"
                            >
                                <h4
                                    class="text-lg font-jost-bold text-dark-gray-500 dark:text-gray-100 mb-4"
                                >
                                    Пошаговая инструкция:
                                </h4>
                                <ol class="space-y-3">
                                    <li
                                        v-for="(step, stepIndex) in faq.steps"
                                        :key="stepIndex"
                                        class="flex items-start"
                                    >
                                        <span
                                            class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center mr-4 mt-1 text-sm font-bold text-blue-600 dark:text-blue-300"
                                        >
                                            {{ stepIndex + 1 }}
                                        </span>
                                        <span class="text-lg">{{ step }}</span>
                                    </li>
                                </ol>
                            </div>
                            <div
                                v-if="faq.contact"
                                class="mt-6 p-4 bg-blue-50/80 backdrop-blur-lg rounded-2xl border border-blue-200/30 dark:bg-blue-900/60 dark:border-blue-700/40"
                            >
                                <p
                                    class="text-lg font-jost-medium text-blue-700 dark:text-blue-300"
                                >
                                    <span class="font-bold">Нужна помощь?</span>
                                    {{ faq.contact }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Results -->
        <div
            v-if="filteredFaqs.length === 0"
            class="no-results text-center py-12"
        >
            <div
                class="w-20 h-20 bg-gray-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6 dark:bg-gray-500/20"
            >
                <span class="text-4xl">😕</span>
            </div>
            <h3
                class="text-2xl font-jost-bold text-gray-500 dark:text-gray-200 mb-4"
            >
                Ничего не найдено
            </h3>
            <p class="text-lg text-gray-500 dark:text-gray-200 mb-6">
                Попробуйте изменить поисковый запрос или выберите другую
                категорию
            </p>
            <button
                @click="clearFilters"
                class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-4 rounded-2xl font-jost-bold text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform"
            >
                Сбросить фильтры
            </button>
        </div>

        <!-- Contact Section -->
        <div class="mt-16 cta-section">
            <div
                class="bg-gradient-to-r from-blue-500/90 to-dark-blue-500/90 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-blue-500/30 text-center"
            >
                <h2 class="text-4xl sm:text-5xl font-jost-bold text-white mb-6">
                    Не нашли ответ?
                </h2>
                <p
                    class="text-xl font-jost-regular text-blue-100 mb-8 max-w-2xl mx-auto"
                >
                    Свяжитесь с нами, и мы поможем решить ваш вопрос
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a
                        :href="`tel:${contacts.phone}`"
                        class="bg-white/20 backdrop-blur-md hover:bg-white/30 text-white px-10 py-5 rounded-2xl font-jost-bold text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform border border-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-blue"
                    >
                        📞 Позвонить
                    </a>
                    <a
                        :href="`mailto:${contacts.email}`"
                        class="bg-white/20 backdrop-blur-md hover:bg-white/30 text-white px-10 py-5 rounded-2xl font-jost-bold text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform border border-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-blue"
                    >
                        📧 Написать
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export default {
    name: "HelpPage",
    props: {
        contacts: {
            type: Object,
            default: () => ({
                phone: "+7 (983) 233-59-07",
                email: "zatochka.tsk@yandex.ru",
                address: "ул. Примерная, д. 123",
                workingHours: "Пн-Сб: 9:00-18:00",
                socialMedia: {
                    telegram: "https://t.me/zatochka_tsk",
                    instagram: "https://instagram.com/zatochka_tsk",
                    vk: "https://vk.com/zatochka_tsk",
                },
            }),
        },
    },
    data() {
        return {
            searchQuery: "",
            selectedCategory: null,
            categories: [
                {
                    id: "general",
                    title: "Общие вопросы",
                    description: "Основная информация о наших услугах",
                    icon: "❓",
                    count: 5,
                },
                {
                    id: "sharpening",
                    title: "Заточка",
                    description: "Вопросы по заточке инструментов",
                    icon: "⚡",
                    count: 4,
                },
                {
                    id: "repair",
                    title: "Ремонт",
                    description: "Вопросы по ремонту оборудования",
                    icon: "🔧",
                    count: 3,
                },
                {
                    id: "delivery",
                    title: "Доставка",
                    description: "Условия и стоимость доставки",
                    icon: "🚚",
                    count: 3,
                },
                {
                    id: "payment",
                    title: "Оплата",
                    description: "Способы и условия оплаты",
                    icon: "💳",
                    count: 2,
                },
                {
                    id: "warranty",
                    title: "Гарантия",
                    description: "Гарантийные обязательства",
                    icon: "🛡️",
                    count: 2,
                },
            ],
            faqs: [
                {
                    id: 1,
                    category: "Общие вопросы",
                    categoryId: "general",
                    icon: "❓",
                    question: "Какие услуги вы предоставляете?",
                    answer: "Мы специализируемся на заточке и ремонте профессиональных инструментов: маникюрных, парикмахерских, грумерских инструментов, а также ремонте оборудования.",
                    steps: [
                        "Заточка маникюрных инструментов (ножницы, кусачки, твизеры)",
                        "Заточка парикмахерских инструментов (прямые, филировочные, конвекс ножницы)",
                        "Заточка груминг инструментов (ножницы, машинки для стрижки шерсти)",
                        "Ремонт маникюрного и парикмахерского оборудования",
                    ],
                    contact:
                        "Свяжитесь с нами для уточнения деталей по телефону +7 (983) 233-59-07",
                },
                {
                    id: 2,
                    category: "Общие вопросы",
                    categoryId: "general",
                    icon: "⏰",
                    question: "Какие у вас рабочие часы?",
                    answer: "Мы работаем с понедельника по субботу с 9:00 до 18:00. Воскресенье - выходной день.",
                    contact: "Для срочных вопросов звоните +7 (983) 233-59-07",
                },
                {
                    id: 3,
                    category: "Заточка",
                    categoryId: "sharpening",
                    icon: "⚡",
                    question: "Сколько времени занимает заточка?",
                    answer: "Время заточки зависит от типа инструмента и его состояния. Обычно это занимает от 1 до 3 дней.",
                    steps: [
                        "Диагностика инструмента (30 минут)",
                        "Заточка (1-2 дня в зависимости от сложности)",
                        "Контроль качества и тестирование",
                        "Упаковка и подготовка к выдаче",
                    ],
                    contact: "Точные сроки уточняйте при сдаче инструмента",
                },
                {
                    id: 4,
                    category: "Заточка",
                    categoryId: "sharpening",
                    icon: "💰",
                    question: "Как рассчитывается стоимость заточки?",
                    answer: "Стоимость зависит от типа инструмента, его состояния и объема работ. Диагностика всегда бесплатная.",
                    steps: [
                        "Бесплатная диагностика инструмента",
                        "Оценка объема работ",
                        "Согласование стоимости с клиентом",
                        "Выполнение работ по утвержденной стоимости",
                    ],
                    contact: "Принесите инструмент для бесплатной оценки",
                },
                {
                    id: 5,
                    category: "Ремонт",
                    categoryId: "repair",
                    icon: "🔧",
                    question: "Какое оборудование вы ремонтируете?",
                    answer: "Мы ремонтируем маникюрное, парикмахерское и грумерское оборудование: машинки для стрижки, фены, электрические ножницы, триммеры, ультразвуковые ванны.",
                    contact:
                        "Позвоните для консультации по ремонту +7 (983) 233-59-07",
                },
                {
                    id: 6,
                    category: "Доставка",
                    categoryId: "delivery",
                    icon: "🚚",
                    question: "Есть ли бесплатная доставка?",
                    answer: "Да, мы предоставляем бесплатную доставку при заказе от 6 маникюрных инструментов или от 3 парикмахерских/грумерских инструментов.",
                    steps: [
                        "От 6 маникюрных инструментов - бесплатно",
                        "От 3 парикмахерских/грумерских инструментов - бесплатно",
                        "Любой аппарат в ремонт - бесплатно",
                        "В остальных случаях - 150 ₽ в одну сторону",
                    ],
                    contact: "Уточните условия доставки по телефону",
                },
                {
                    id: 7,
                    category: "Доставка",
                    categoryId: "delivery",
                    icon: "📅",
                    question: "Когда работает доставка?",
                    answer: "Доставка работает в рабочие дни: понедельник, вторник, среда, пятница, суббота с 13:00 до 17:00.",
                    contact: "Согласуйте время доставки заранее",
                },
                {
                    id: 8,
                    category: "Оплата",
                    categoryId: "payment",
                    icon: "💳",
                    question: "Какие способы оплаты вы принимаете?",
                    answer: "Мы принимаем оплату наличными, банковскими картами и переводом на карту. Оплата производится при получении готовых инструментов.",
                    contact: "Уточните удобный способ оплаты при заказе",
                },
                {
                    id: 9,
                    category: "Гарантия",
                    categoryId: "warranty",
                    icon: "🛡️",
                    question: "Какая гарантия на ваши работы?",
                    answer: "На заточку инструментов - 30 дней гарантии. На ремонт оборудования - 90 дней гарантии. Претензии принимаются в течение указанного срока.",
                    contact: "При возникновении проблем обращайтесь сразу",
                },
            ],
        };
    },
    computed: {
        filteredFaqs() {
            let filtered = this.faqs;

            // Filter by category
            if (this.selectedCategory) {
                filtered = filtered.filter(
                    (faq) => faq.categoryId === this.selectedCategory
                );
            }

            // Filter by search query
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(
                    (faq) =>
                        faq.question.toLowerCase().includes(query) ||
                        faq.answer.toLowerCase().includes(query) ||
                        faq.category.toLowerCase().includes(query)
                );
            }

            return filtered;
        },
    },
    watch: {
        searchQuery() {
            // Анимация при изменении поискового запроса
            this.$nextTick(() => {
                this.animateSearchResults();
            });
        },
        filteredFaqs() {
            // Анимация при изменении результатов
            this.$nextTick(() => {
                this.animateSearchResults();
            });
        },
    },
    mounted() {
        this.initAnimations();
    },
    methods: {
        initAnimations() {
            // Анимация поиска
            gsap.fromTo(
                ".search-section",
                { opacity: 0, y: 30 },
                { opacity: 1, y: 0, duration: 0.8, ease: "power2.out" }
            );

            // Анимация категорий
            gsap.fromTo(
                ".category-card",
                {
                    opacity: 0,
                    y: 50,
                    scale: 0.9,
                },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.8,
                    ease: "back.out(1.7)",
                    stagger: 0.1,
                    delay: 0.3,
                }
            );

            // Анимация FAQ элементов
            gsap.fromTo(
                ".faq-item",
                {
                    opacity: 0,
                    x: -30,
                },
                {
                    opacity: 1,
                    x: 0,
                    duration: 0.6,
                    ease: "power2.out",
                    stagger: 0.1,
                    delay: 0.6,
                    scrollTrigger: {
                        trigger: ".faq-item",
                        start: "top 80%",
                        toggleActions: "play none none reverse",
                    },
                }
            );

            // Анимация CTA секции
            gsap.fromTo(
                ".cta-section",
                {
                    opacity: 0,
                    y: 50,
                    scale: 0.95,
                },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 1,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: ".cta-section",
                        start: "top 80%",
                        toggleActions: "play none none reverse",
                    },
                }
            );
        },

        animateCategorySelect(categoryId) {
            const categoryCards = document.querySelectorAll(".category-card");

            categoryCards.forEach((card, index) => {
                if (card.dataset.categoryId === categoryId) {
                    // Анимация выбранной категории
                    gsap.to(card, {
                        scale: 1.05,
                        duration: 0.3,
                        ease: "power2.out",
                        yoyo: true,
                        repeat: 1,
                    });
                } else {
                    // Анимация невыбранных категорий
                    gsap.to(card, {
                        scale: 0.95,
                        opacity: 0.7,
                        duration: 0.3,
                        ease: "power2.out",
                    });
                }
            });
        },

        animateFaqToggle(faqId, isOpen) {
            const faqElement = document.querySelector(
                `[data-faq-id="${faqId}"]`
            );
            const faqContent = faqElement?.querySelector(".faq-content");

            if (!faqContent) return;

            if (isOpen) {
                // Анимация открытия
                gsap.fromTo(
                    faqContent,
                    {
                        height: 0,
                        opacity: 0,
                    },
                    {
                        height: "auto",
                        opacity: 1,
                        duration: 0.5,
                        ease: "power2.out",
                    }
                );

                // Анимация содержимого
                gsap.fromTo(
                    faqContent.children,
                    {
                        opacity: 0,
                        y: 20,
                    },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.4,
                        ease: "power2.out",
                        stagger: 0.1,
                        delay: 0.2,
                    }
                );
            } else {
                // Анимация содержимого при закрытии
                gsap.to(faqContent.children, {
                    opacity: 0,
                    y: -10,
                    duration: 0.2,
                    ease: "power2.in",
                });

                // Анимация закрытия контейнера
                gsap.to(faqContent, {
                    height: 0,
                    opacity: 0,
                    duration: 0.3,
                    ease: "power2.in",
                    delay: 0.1,
                });
            }
        },

        animateSearchResults() {
            const faqItems = document.querySelectorAll(".faq-item");
            const ctaSection = document.querySelector(".cta-section");
            const noResults = document.querySelector(".no-results");

            // Анимация FAQ элементов
            if (faqItems.length > 0) {
                gsap.fromTo(
                    faqItems,
                    {
                        opacity: 0,
                        y: 30,
                        scale: 0.95,
                    },
                    {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: 0.5,
                        ease: "back.out(1.7)",
                        stagger: 0.1,
                    }
                );
            }

            // Анимация блока "Ничего не найдено"
            if (noResults) {
                gsap.fromTo(
                    noResults,
                    {
                        opacity: 0,
                        y: 50,
                        scale: 0.9,
                    },
                    {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: 0.6,
                        ease: "back.out(1.7)",
                    }
                );
            }

            // Анимация CTA секции
            if (ctaSection) {
                gsap.fromTo(
                    ctaSection,
                    {
                        opacity: 0,
                        y: 50,
                        scale: 0.95,
                    },
                    {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: 0.8,
                        ease: "power2.out",
                        delay: 0.3,
                    }
                );
            }
        },

        selectCategory(categoryId) {
            this.selectedCategory =
                this.selectedCategory === categoryId ? null : categoryId;

            this.animateCategorySelect(categoryId);

            this.$nextTick(() => {
                this.animateSearchResults();
            });
        },

        toggleFaq(faqId) {
            const faq = this.faqs.find((f) => f.id === faqId);
            if (faq) {
                faq.isOpen = !faq.isOpen;

                // Анимация стрелки
                const faqElement = document.querySelector(
                    `[data-faq-id="${faqId}"]`
                );
                const arrow = faqElement?.querySelector(".faq-arrow");

                if (arrow) {
                    gsap.to(arrow, {
                        rotation: faq.isOpen ? 180 : 0,
                        duration: 0.3,
                        ease: "power2.out",
                    });
                }

                // Анимация переключения FAQ
                this.$nextTick(() => {
                    this.animateFaqToggle(faqId, faq.isOpen);
                });
            }
        },

        clearFilters() {
            this.searchQuery = "";
            this.selectedCategory = null;

            // Анимация сброса фильтров
            gsap.to(".category-card", {
                scale: 1,
                opacity: 1,
                duration: 0.5,
                ease: "power2.out",
            });

            this.$nextTick(() => {
                this.animateSearchResults();
            });
        },
    },
};
</script>
