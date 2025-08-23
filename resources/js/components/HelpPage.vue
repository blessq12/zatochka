<template>
    <div class="help-page">
        <!-- Основной контент -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4">
                <!-- Поиск -->
                <div class="mb-12" ref="searchSection">
                    <div class="max-w-2xl mx-auto">
                        <div class="relative">
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Поиск по вопросам..."
                                class="w-full px-6 py-4 pl-14 text-lg border-2 border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                                @input="filterQuestions"
                            />
                            <i
                                class="mdi mdi-magnify absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"
                            ></i>
                        </div>
                        <div v-if="searchQuery" class="mt-4 text-center">
                            <span class="text-gray-600 dark:text-gray-400">
                                Найдено: {{ filteredQuestions.length }} вопросов
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Основной контент с боковой навигацией -->
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Боковая навигация -->
                    <div class="lg:w-80 flex-shrink-0">
                        <div class="sticky top-8">
                            <h2
                                class="text-xl font-bold text-gray-900 dark:text-white mb-6"
                            >
                                Разделы помощи
                            </h2>
                            <nav class="space-y-2">
                                <a
                                    v-for="(category, index) in categories"
                                    :key="category.id"
                                    :href="`#${category.id}`"
                                    class="sidebar-nav-item"
                                    :class="{
                                        active: activeCategory === category.id,
                                    }"
                                    @click.prevent="
                                        setActiveCategory(category.id)
                                    "
                                >
                                    <i
                                        :class="`${category.icon} text-lg mr-3`"
                                    ></i>
                                    <div>
                                        <div class="font-semibold">
                                            {{ category.title }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            {{ category.description }}
                                        </div>
                                    </div>
                                </a>
                            </nav>
                        </div>
                    </div>

                    <!-- Основной контент -->
                    <div class="flex-1">
                        <!-- FAQ секция -->
                        <div class="space-y-8" ref="faqSection">
                            <div
                                v-for="(category, categoryIndex) in categories"
                                :key="category.id"
                                :id="category.id"
                                class="help-section"
                                :ref="`section${categoryIndex}`"
                            >
                                <h2
                                    class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center"
                                >
                                    <i
                                        :class="`${category.icon} text-accent mr-3`"
                                    ></i>
                                    {{ category.title }}
                                </h2>

                                <div class="space-y-4">
                                    <div
                                        v-for="(
                                            question, questionIndex
                                        ) in getCategoryQuestions(category.id)"
                                        :key="question.id"
                                        class="help-item"
                                        :ref="`question${categoryIndex}_${questionIndex}`"
                                    >
                                        <div
                                            class="question-header"
                                            @click="toggleQuestion(question.id)"
                                            :class="{
                                                active: openQuestions.includes(
                                                    question.id
                                                ),
                                            }"
                                        >
                                            <h3
                                                class="text-lg font-semibold text-gray-800 dark:text-white"
                                            >
                                                {{ question.title }}
                                            </h3>
                                            <i
                                                class="mdi mdi-chevron-down transition-transform duration-300"
                                            ></i>
                                        </div>
                                        <div
                                            class="question-content"
                                            :class="{
                                                open: openQuestions.includes(
                                                    question.id
                                                ),
                                            }"
                                            :ref="`content${categoryIndex}_${questionIndex}`"
                                        >
                                            <div
                                                class="text-gray-700 dark:text-gray-300"
                                                v-html="question.content"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Кнопка "Наверх" -->
                <div
                    v-show="showScrollTop"
                    class="fixed bottom-8 right-8 z-50"
                    ref="scrollTopButton"
                >
                    <button
                        @click="scrollToTop"
                        class="bg-accent text-white p-4 rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
                    >
                        <i class="mdi mdi-chevron-up text-xl"></i>
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import { gsap } from "gsap";

export default {
    name: "HelpPage",
    data() {
        return {
            searchQuery: "",
            activeCategory: "ordering",
            openQuestions: [],
            showScrollTop: false,
            categories: [
                {
                    id: "ordering",
                    title: "Оформление заказа",
                    description: "Как заказать услуги",
                    icon: "mdi mdi-cart-plus",
                },
                {
                    id: "services",
                    title: "Наши услуги",
                    description: "Что мы делаем",
                    icon: "mdi mdi-tools",
                },
                {
                    id: "delivery",
                    title: "Доставка",
                    description: "Условия доставки",
                    icon: "mdi mdi-truck-delivery",
                },
                {
                    id: "payment",
                    title: "Оплата",
                    description: "Способы оплаты",
                    icon: "mdi mdi-credit-card",
                },
                {
                    id: "account",
                    title: "Личный кабинет",
                    description: "Управление заказами",
                    icon: "mdi mdi-account-circle",
                    isAuth: false, // Здесь будет определяться статус авторизации
                },
                {
                    id: "contact",
                    title: "Контакты",
                    description: "Как связаться",
                    icon: "mdi mdi-phone",
                },
            ],
            questions: [
                {
                    id: "sharpening-order",
                    category: "ordering",
                    title: "Как оформить заказ на заточку инструмента?",
                    content: `
                        <div class="space-y-2">
                            <p>1. Перейдите на страницу "Заточка инструментов"</p>
                            <p>2. Заполните форму заказа, указав:</p>
                            <ul class="list-disc pl-6 ml-4">
                                <li>Ваше имя и телефон</li>
                                <li>Тип и количество инструментов</li>
                                <li>Описание требуемых работ</li>
                                <li>Комментарии (при необходимости)</li>
                            </ul>
                            <p>3. Поставьте галочки в согласиях</p>
                            <p>4. Нажмите "Отправить заявку"</p>
                        </div>
                    `,
                },
                {
                    id: "repair-order",
                    category: "ordering",
                    title: "Как оформить заказ на ремонт инструмента?",
                    content: `
                        <div class="space-y-2">
                            <p>1. Перейдите на страницу "Ремонт инструментов"</p>
                            <p>2. Заполните форму заказа, указав:</p>
                            <ul class="list-disc pl-6 ml-4">
                                <li>Ваше имя и телефон</li>
                                <li>Тип инструмента и характер поломки</li>
                                <li>Срочность ремонта</li>
                                <li>Подробное описание проблемы</li>
                            </ul>
                            <p>3. Поставьте галочки в согласиях</p>
                            <p>4. Нажмите "Отправить заявку"</p>
                        </div>
                    `,
                },
                {
                    id: "after-order",
                    category: "ordering",
                    title: "Что делать после отправки заявки?",
                    content:
                        "<p>После отправки заявки мы свяжемся с вами в течение 1-2 часов для уточнения деталей и согласования времени приема инструмента. Также мы сообщим предварительную стоимость работ.</p>",
                },
                {
                    id: "sharpening-tools",
                    category: "services",
                    title: "Какие инструменты мы затачиваем?",
                    content: `
                        <ul class="list-disc pl-6">
                            <li>Ножи (кухонные, охотничьи, складные)</li>
                            <li>Ножницы (бытовые, парикмахерские, портновские)</li>
                            <li>Топоры и тесаки</li>
                            <li>Сверла и буры</li>
                            <li>Пилы (ручные, цепные)</li>
                            <li>Стамески и долота</li>
                            <li>И другие режущие инструменты</li>
                        </ul>
                    `,
                },
                {
                    id: "repair-types",
                    category: "services",
                    title: "Какие виды ремонта мы выполняем?",
                    content: `
                        <ul class="list-disc pl-6">
                            <li>Восстановление режущих кромок</li>
                            <li>Ремонт рукояток и креплений</li>
                            <li>Замена деталей</li>
                            <li>Восстановление геометрии инструмента</li>
                            <li>Реставрация старых инструментов</li>
                        </ul>
                    `,
                },
                {
                    id: "timing",
                    category: "services",
                    title: "Сколько времени занимает заточка/ремонт?",
                    content: `
                        <p><strong>Обычная заточка:</strong> 1-3 дня в зависимости от сложности</p>
                        <p><strong>Срочная заточка:</strong> в день обращения (при наличии времени)</p>
                        <p><strong>Ремонт:</strong> 2-7 дней в зависимости от характера работ</p>
                    `,
                },
                {
                    id: "delivery-service",
                    category: "delivery",
                    title: "Есть ли услуга доставки?",
                    content:
                        '<p>Да, мы предоставляем услугу доставки инструментов. При оформлении заказа вы можете выбрать опцию "Доставка" и указать удобное время и адрес.</p>',
                },
                {
                    id: "delivery-cost",
                    category: "delivery",
                    title: "Сколько стоит доставка?",
                    content:
                        "<p>Стоимость доставки зависит от района города и оговаривается индивидуально при оформлении заказа. Обычно составляет 200-500 рублей.</p>",
                },
                {
                    id: "self-delivery",
                    category: "delivery",
                    title: "Можно ли привезти инструмент самостоятельно?",
                    content:
                        "<p>Конечно! Вы можете привезти инструмент в нашу мастерскую по адресу: Пр. Ленина 169/пер. Карповский 12, Томск. Вход со стороны Ленина, ориентир — магазин «Тайга».</p>",
                },
                {
                    id: "payment-methods",
                    category: "payment",
                    title: "Какие способы оплаты принимаются?",
                    content: `
                        <ul class="list-disc pl-6">
                            <li>Наличные деньги</li>
                            <li>Банковские карты (Visa, MasterCard, МИР)</li>
                            <li>Безналичный расчет для юридических лиц</li>
                        </ul>
                    `,
                },
                {
                    id: "payment-timing",
                    category: "payment",
                    title: "Когда производится оплата?",
                    content:
                        "<p>Оплата производится после выполнения работ при получении инструмента. Предоплата не требуется.</p>",
                },
                {
                    id: "warranty",
                    category: "payment",
                    title: "Есть ли гарантия на работы?",
                    content:
                        "<p>Да, мы предоставляем гарантию на все выполненные работы. При обнаружении недостатков в течение гарантийного срока мы бесплатно устраним их.</p>",
                },
                {
                    id: "account-login",
                    category: "account",
                    title: "Как войти в личный кабинет?",
                    content:
                        "<p>Для входа в личный кабинет используйте номер телефона, указанный при оформлении заказа. Система автоматически создаст аккаунт при первом заказе.</p>",
                },
                {
                    id: "account-features",
                    category: "account",
                    title: "Что можно делать в личном кабинете?",
                    content: `
                        <ul class="list-disc pl-6">
                            <li>Просматривать историю заказов</li>
                            <li>Отслеживать статус текущих заказов</li>
                            <li>Повторять предыдущие заказы</li>
                            <li>Редактировать контактные данные</li>
                        </ul>
                    `,
                },
                {
                    id: "contact-info",
                    category: "contact",
                    title: "Как с нами связаться?",
                    content: `
                        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                            <p class="mb-2"><strong>Телефон:</strong> +7 (983) 233-59-07 (Максим)</p>
                            <p class="mb-2"><strong>Email:</strong> zatochka.tsk@yandex.ru</p>
                            <p class="mb-2"><strong>Адрес:</strong> Пр. Ленина 169/пер. Карповский 12, Томск</p>
                            <p><strong>Время работы:</strong> Пн-Пт: 9:00-18:00, Сб: 9:00-16:00</p>
                        </div>
                    `,
                },
                {
                    id: "contact-support",
                    category: "contact",
                    title: "Не нашли ответ на свой вопрос?",
                    content: `
                        <p>Если вы не нашли ответ на свой вопрос в этом разделе, свяжитесь с нами любым удобным способом. Мы обязательно поможем вам!</p>
                        <div class="mt-4">
                            <a href="/contacts" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-accent to-pink-600 text-white font-semibold rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                                <i class="mdi mdi-phone mr-2"></i>
                                Связаться с нами
                            </a>
                        </div>
                    `,
                },
            ],
        };
    },
    computed: {
        filteredQuestions() {
            if (!this.searchQuery) {
                return this.questions;
            }
            const query = this.searchQuery.toLowerCase();
            return this.questions.filter(
                (question) =>
                    question.title.toLowerCase().includes(query) ||
                    question.content.toLowerCase().includes(query)
            );
        },
    },
    mounted() {
        this.initAnimations();
        this.initScrollListener();
    },
    methods: {
        initAnimations() {
            // Анимация появления элементов
            gsap.fromTo(
                this.$refs.searchSection,
                { opacity: 0, y: 30 },
                { opacity: 1, y: 0, duration: 0.6, ease: "power2.out" }
            );

            // Анимация FAQ секций
            const sections = this.categories
                .map((_, index) => this.$refs[`section${index}`])
                .filter(Boolean);
            gsap.fromTo(
                sections,
                { opacity: 0, y: 40 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.6,
                    stagger: 0.2,
                    delay: 0.2,
                    ease: "power2.out",
                }
            );
        },

        initScrollListener() {
            window.addEventListener("scroll", () => {
                this.showScrollTop = window.scrollY > 300;
                this.updateActiveCategory();
            });
        },

        updateActiveCategory() {
            const sections = this.categories.map((cat) =>
                document.getElementById(cat.id)
            );
            const scrollPosition = window.scrollY + 100;

            for (let i = sections.length - 1; i >= 0; i--) {
                const section = sections[i];
                if (section && section.offsetTop <= scrollPosition) {
                    this.activeCategory = this.categories[i].id;
                    break;
                }
            }
        },

        filterQuestions() {
            // Простая фильтрация без анимации для производительности
            // Результаты обновляются автоматически через computed свойство
        },

        setActiveCategory(categoryId) {
            this.activeCategory = categoryId;

            // Прокрутка к секции
            const section = document.getElementById(categoryId);
            if (section) {
                section.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                    inline: "nearest",
                });
            }
        },

        toggleQuestion(questionId) {
            const isOpen = this.openQuestions.includes(questionId);

            if (isOpen) {
                // Закрываем текущий вопрос
                this.openQuestions = this.openQuestions.filter(
                    (id) => id !== questionId
                );
            } else {
                // Закрываем все остальные вопросы и открываем только текущий
                this.openQuestions = [questionId];
            }
        },

        getCategoryQuestions(categoryId) {
            return this.filteredQuestions.filter(
                (q) => q.category === categoryId
            );
        },

        scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        },
    },
};
</script>

<style scoped>
.sidebar-nav-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 0.5rem;
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    transition: all 0.3s;
    cursor: pointer;
    text-decoration: none;
    color: #374151;
}

.dark .sidebar-nav-item {
    background-color: #1f2937;
    border-color: #374151;
    color: #d1d5db;
}

.sidebar-nav-item.active {
    background-color: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
}

.dark .sidebar-nav-item.active {
    background-color: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
}

.help-nav-card {
    background-color: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
    border: 1px solid #e5e7eb;
    text-align: center;
    cursor: pointer;
}

.dark .help-nav-card {
    background-color: #1f2937;
    border-color: #374151;
}

.help-nav-card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    transform: translateY(-0.25rem);
    border-color: #f59e0b;
}

.help-nav-card.active {
    border-color: #f59e0b;
    background-color: rgba(245, 158, 11, 0.05);
}

.help-section {
    scroll-margin-top: 5rem;
}

.help-item {
    background-color: #f9fafb;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.dark .help-item {
    background-color: #1f2937;
    border-color: #374151;
}

.question-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.question-header.active i {
    transform: rotate(180deg);
}

.question-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.question-content.open {
    max-height: 500px;
}

.question-content > div {
    padding: 1.5rem;
    padding-top: 0;
}
</style>
