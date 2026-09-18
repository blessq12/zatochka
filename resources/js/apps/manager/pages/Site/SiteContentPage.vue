<script>
import { siteContentService } from "../../services/SiteContentService.js";

const TABS = [
    { id: "company", label: "Компания" },
    { id: "contacts", label: "Контакты" },
    { id: "schedule", label: "График" },
    { id: "faq", label: "FAQ" },
    { id: "delivery", label: "Доставка" },
    { id: "prices", label: "Прайс" },
    { id: "legal", label: "Юр. документы" },
];

export default {
    name: "SiteContentPage",
    data() {
        return {
            tabs: TABS,
            tab: "company",
            loading: false,
            saving: false,
            error: null,
            success: null,
            company: {
                name: "",
                tagline: "",
                owner_name: "",
                inn: "",
                ogrn: "",
                legal_address: "",
                actual_address: "",
            },
            contacts: {
                phone: "",
                email: "",
                contact_person: "",
                address: { main: "", directions: "" },
                social: { email: "", links: [] },
            },
            scheduleDays: [],
            faqItems: [],
            delivery: {
                free_conditions: [],
                advantages: [],
            },
            prices: [],
            legal: [],
            legalForm: {
                slug: "",
                type: "",
                title: "",
                body_html: "",
            },
        };
    },
    mounted() {
        this.load();
    },
    computed: {
        currentTabLabel() {
            return this.tabs.find((item) => item.id === this.tab)?.label || "";
        },
    },
    methods: {
        selectTab(id) {
            this.tab = id;
            this.success = null;
            this.error = null;
        },
        sectionButtonClass(id) {
            return this.tab === id
                ? "app-btn-primary app-action-btn"
                : "app-btn-secondary app-action-btn";
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                const data = await siteContentService.get();
                const raw = data.raw || {};
                this.company = { ...this.company, ...(raw.company || {}) };
                this.contacts = {
                    phone: raw.contacts?.phone || "",
                    email: raw.contacts?.email || "",
                    contact_person: raw.contacts?.contact_person || "",
                    address: {
                        main: raw.contacts?.address?.main || "",
                        directions: raw.contacts?.address?.directions || "",
                    },
                    social: {
                        email: raw.contacts?.social?.email || "",
                        links: [...(raw.contacts?.social?.links || [])],
                    },
                };
                this.scheduleDays = [...(raw.schedule?.days || [])];
                this.faqItems = (raw.faq?.items || []).map((item) => ({
                    question: item.question || "",
                    answer_text: (item.answer_lines || []).join("\n"),
                }));
                this.delivery = {
                    free_conditions: [...(raw.delivery_info?.free_conditions || [])],
                    advantages: [...(raw.delivery_info?.advantages || [])],
                };
                this.prices = [...(raw.prices || [])];
                this.legal = [...(data.legal || [])];
            } catch (e) {
                this.error = e.response?.data?.message || "Не удалось загрузить контент";
            } finally {
                this.loading = false;
            }
        },
        async saveCurrent() {
            this.saving = true;
            this.error = null;
            this.success = null;
            try {
                if (this.tab === "company") {
                    await siteContentService.updateSection("company", this.company);
                } else if (this.tab === "contacts") {
                    await siteContentService.updateSection("contacts", this.contacts);
                } else if (this.tab === "schedule") {
                    await siteContentService.updateSection("schedule", {
                        days: this.scheduleDays,
                    });
                } else if (this.tab === "faq") {
                    await siteContentService.updateSection("faq", {
                        items: this.faqItems.map((item) => ({
                            question: item.question,
                            answer_lines: item.answer_text,
                        })),
                    });
                } else if (this.tab === "delivery") {
                    await siteContentService.updateSection("delivery", this.delivery);
                } else if (this.tab === "prices") {
                    await siteContentService.updateSection("prices", {
                        items: this.prices,
                    });
                }
                this.success = "Сохранено";
                await this.load();
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    (e.response?.data?.errors
                        ? Object.values(e.response.data.errors).flat().join(" ")
                        : "Ошибка сохранения");
            } finally {
                this.saving = false;
            }
        },
        addLink() {
            this.contacts.social.links.push({ name: "", url: "" });
        },
        removeLink(index) {
            this.contacts.social.links.splice(index, 1);
        },
        addDay() {
            this.scheduleDays.push({ label: "", hours: "" });
        },
        removeDay(index) {
            this.scheduleDays.splice(index, 1);
        },
        addFaq() {
            this.faqItems.push({ question: "", answer_text: "" });
        },
        removeFaq(index) {
            this.faqItems.splice(index, 1);
        },
        addCondition() {
            this.delivery.free_conditions.push("");
        },
        removeCondition(index) {
            this.delivery.free_conditions.splice(index, 1);
        },
        addAdvantage() {
            this.delivery.advantages.push({ title: "", text: "" });
        },
        removeAdvantage(index) {
            this.delivery.advantages.splice(index, 1);
        },
        addPrice() {
            this.prices.push({
                category: "sharpening",
                name: "",
                description: "",
                price: "",
                prefix: null,
            });
        },
        removePrice(index) {
            this.prices.splice(index, 1);
        },
        editLegal(doc) {
            this.legalForm = {
                slug: doc.slug,
                type: doc.type,
                title: doc.title,
                body_html: doc.body_html,
            };
        },
        resetLegal() {
            this.legalForm = { slug: "", type: "", title: "", body_html: "" };
        },
        async saveLegal() {
            this.saving = true;
            this.error = null;
            this.success = null;
            try {
                await siteContentService.saveLegal({
                    ...this.legalForm,
                    type: this.legalForm.type || this.legalForm.slug,
                });
                this.success = "Документ сохранён";
                this.resetLegal();
                await this.load();
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    (e.response?.data?.errors
                        ? Object.values(e.response.data.errors).flat().join(" ")
                        : "Ошибка сохранения документа");
            } finally {
                this.saving = false;
            }
        },
        async removeLegal(slug) {
            if (!confirm(`Удалить документ «${slug}»?`)) return;
            try {
                await siteContentService.deleteLegal(slug);
                await this.load();
            } catch (e) {
                this.error = e.response?.data?.message || "Не удалось удалить";
            }
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Контент сайта</h1>
        </div>

        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        <p v-if="success" class="text-sm text-green-700">{{ success }}</p>
        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>

        <div
            v-else
            class="grid gap-4 lg:grid-cols-[minmax(16rem,20rem)_minmax(0,1fr)] lg:items-start lg:gap-6"
        >
            <aside class="space-y-3 lg:sticky lg:top-4">
                <div
                    class="space-y-2 border border-slate-300 bg-white p-3 text-sm shadow-sm lg:p-4"
                >
                    <p class="font-jost-medium text-dark-blue-500">
                        Разделы сайта
                    </p>
                    <p class="text-xs text-slate-500">
                        Сейчас: {{ currentTabLabel }}
                    </p>
                </div>

                <section
                    class="space-y-2 border border-slate-300 bg-white p-3 shadow-sm lg:p-4"
                >
                    <h2 class="text-sm font-jost-bold text-dark-blue-500">
                        Навигация
                    </h2>
                    <div class="flex flex-col gap-2">
                        <button
                            v-for="item in tabs"
                            :key="item.id"
                            type="button"
                            :class="sectionButtonClass(item.id)"
                            @click="selectTab(item.id)"
                        >
                            <span class="app-action-btn-title">
                                {{ item.label }}
                            </span>
                        </button>
                    </div>
                </section>

                <section
                    v-if="tab !== 'legal'"
                    class="space-y-2 border border-slate-300 bg-white p-3 shadow-sm lg:p-4"
                >
                    <h2 class="text-sm font-jost-bold text-dark-blue-500">
                        Действия
                    </h2>
                    <button
                        type="button"
                        class="app-btn-primary w-full"
                        :disabled="saving || loading"
                        @click="saveCurrent"
                    >
                        {{ saving ? "Сохранение…" : "Сохранить раздел" }}
                    </button>
                </section>
            </aside>

            <div
                class="min-w-0 space-y-4 border border-slate-300 bg-white p-3 shadow-sm sm:p-4"
            >
                <h2
                    class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                >
                    {{ currentTabLabel }}
                </h2>

                <template v-if="tab === 'company'">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="block space-y-1 sm:col-span-2">
                            <span class="text-sm text-slate-600">Название</span>
                            <input
                                v-model="company.name"
                                type="text"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1 sm:col-span-2">
                            <span class="text-sm text-slate-600">Слоган</span>
                            <input
                                v-model="company.tagline"
                                type="text"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1">
                            <span class="text-sm text-slate-600">Владелец</span>
                            <input
                                v-model="company.owner_name"
                                type="text"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1">
                            <span class="text-sm text-slate-600">ИНН</span>
                            <input
                                v-model="company.inn"
                                type="text"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1">
                            <span class="text-sm text-slate-600">ОГРН</span>
                            <input
                                v-model="company.ogrn"
                                type="text"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1 sm:col-span-2">
                            <span class="text-sm text-slate-600">Юр. адрес</span>
                            <input
                                v-model="company.legal_address"
                                type="text"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1 sm:col-span-2">
                            <span class="text-sm text-slate-600"
                                >Факт. адрес</span
                            >
                            <input
                                v-model="company.actual_address"
                                type="text"
                                class="app-field"
                            />
                        </label>
                    </div>
                </template>

                <template v-else-if="tab === 'contacts'">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="block space-y-1">
                            <span class="text-sm text-slate-600">Телефон</span>
                            <input
                                v-model="contacts.phone"
                                type="tel"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1">
                            <span class="text-sm text-slate-600">Email</span>
                            <input
                                v-model="contacts.email"
                                type="email"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1 sm:col-span-2">
                            <span class="text-sm text-slate-600"
                                >Контактное лицо</span
                            >
                            <input
                                v-model="contacts.contact_person"
                                type="text"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1 sm:col-span-2">
                            <span class="text-sm text-slate-600">Адрес</span>
                            <input
                                v-model="contacts.address.main"
                                type="text"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1 sm:col-span-2">
                            <span class="text-sm text-slate-600">Как пройти</span>
                            <textarea
                                v-model="contacts.address.directions"
                                rows="3"
                                class="app-field"
                            />
                        </label>
                        <label class="block space-y-1 sm:col-span-2">
                            <span class="text-sm text-slate-600"
                                >Email для соцблока</span
                            >
                            <input
                                v-model="contacts.social.email"
                                type="email"
                                class="app-field"
                            />
                        </label>
                    </div>
                    <div class="space-y-2 border-t border-slate-100 pt-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-jost-medium text-dark-blue-500">
                                Соцссылки
                            </h3>
                            <button
                                type="button"
                                class="text-sm text-pink-700 hover:underline"
                                @click="addLink"
                            >
                                + ссылка
                            </button>
                        </div>
                        <div
                            v-for="(link, index) in contacts.social.links"
                            :key="index"
                            class="flex flex-col gap-2 sm:flex-row"
                        >
                            <input
                                v-model="link.name"
                                type="text"
                                placeholder="Название"
                                class="app-field sm:w-1/3"
                            />
                            <input
                                v-model="link.url"
                                type="url"
                                placeholder="URL"
                                class="app-field flex-1"
                            />
                            <button
                                type="button"
                                class="text-sm text-red-700 hover:underline sm:px-2"
                                @click="removeLink(index)"
                            >
                                Убрать
                            </button>
                        </div>
                    </div>
                </template>

                <template v-else-if="tab === 'schedule'">
                    <div class="flex justify-end">
                        <button
                            type="button"
                            class="text-sm text-pink-700 hover:underline"
                            @click="addDay"
                        >
                            + день
                        </button>
                    </div>
                    <div
                        v-for="(day, index) in scheduleDays"
                        :key="index"
                        class="flex flex-col gap-2 sm:flex-row"
                    >
                        <input
                            v-model="day.label"
                            type="text"
                            placeholder="Пн–Пт"
                            class="app-field sm:w-1/3"
                        />
                        <input
                            v-model="day.hours"
                            type="text"
                            placeholder="10:00–19:00"
                            class="app-field flex-1"
                        />
                        <button
                            type="button"
                            class="text-sm text-red-700 hover:underline sm:px-2"
                            @click="removeDay(index)"
                        >
                            Убрать
                        </button>
                    </div>
                </template>

                <template v-else-if="tab === 'faq'">
                    <div class="flex justify-end">
                        <button
                            type="button"
                            class="text-sm text-pink-700 hover:underline"
                            @click="addFaq"
                        >
                            + вопрос
                        </button>
                    </div>
                    <div
                        v-for="(item, index) in faqItems"
                        :key="index"
                        class="space-y-2 border border-slate-200 p-3"
                    >
                        <input
                            v-model="item.question"
                            type="text"
                            placeholder="Вопрос"
                            class="app-field"
                        />
                        <textarea
                            v-model="item.answer_text"
                            rows="3"
                            placeholder="Ответ (строки с новой строки)"
                            class="app-field"
                        />
                        <button
                            type="button"
                            class="text-sm text-red-700 hover:underline"
                            @click="removeFaq(index)"
                        >
                            Удалить
                        </button>
                    </div>
                </template>

                <template v-else-if="tab === 'delivery'">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-jost-medium text-dark-blue-500">
                                Условия бесплатной доставки
                            </h3>
                            <button
                                type="button"
                                class="text-sm text-pink-700 hover:underline"
                                @click="addCondition"
                            >
                                + условие
                            </button>
                        </div>
                        <div
                            v-for="(_, index) in delivery.free_conditions"
                            :key="'c' + index"
                            class="flex gap-2"
                        >
                            <input
                                v-model="delivery.free_conditions[index]"
                                type="text"
                                class="app-field flex-1"
                            />
                            <button
                                type="button"
                                class="text-sm text-red-700 hover:underline"
                                @click="removeCondition(index)"
                            >
                                Убрать
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2 border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-jost-medium text-dark-blue-500">
                                Преимущества
                            </h3>
                            <button
                                type="button"
                                class="text-sm text-pink-700 hover:underline"
                                @click="addAdvantage"
                            >
                                + преимущество
                            </button>
                        </div>
                        <div
                            v-for="(row, index) in delivery.advantages"
                            :key="'a' + index"
                            class="space-y-2 border border-slate-200 p-3"
                        >
                            <input
                                v-model="row.title"
                                type="text"
                                placeholder="Заголовок"
                                class="app-field"
                            />
                            <textarea
                                v-model="row.text"
                                rows="2"
                                placeholder="Текст"
                                class="app-field"
                            />
                            <button
                                type="button"
                                class="text-sm text-red-700 hover:underline"
                                @click="removeAdvantage(index)"
                            >
                                Удалить
                            </button>
                        </div>
                    </div>
                </template>

                <template v-else-if="tab === 'prices'">
                    <div class="flex justify-end">
                        <button
                            type="button"
                            class="text-sm text-pink-700 hover:underline"
                            @click="addPrice"
                        >
                            + позиция
                        </button>
                    </div>
                    <div
                        v-for="(item, index) in prices"
                        :key="index"
                        class="grid gap-2 border border-slate-200 p-3 sm:grid-cols-6"
                    >
                        <select
                            v-model="item.category"
                            class="app-field sm:col-span-1"
                        >
                            <option value="sharpening">Заточка</option>
                            <option value="repair">Ремонт</option>
                        </select>
                        <input
                            v-model="item.name"
                            type="text"
                            placeholder="Название"
                            class="app-field sm:col-span-2"
                        />
                        <input
                            v-model="item.description"
                            type="text"
                            placeholder="Описание"
                            class="app-field sm:col-span-1"
                        />
                        <input
                            v-model="item.price"
                            type="text"
                            placeholder="Цена"
                            class="app-field"
                        />
                        <div class="flex gap-2">
                            <select v-model="item.prefix" class="app-field flex-1">
                                <option :value="null">—</option>
                                <option value="from">от</option>
                                <option value="to">до</option>
                            </select>
                            <button
                                type="button"
                                class="shrink-0 text-sm text-red-700 hover:underline"
                                @click="removePrice(index)"
                            >
                                Убрать
                            </button>
                        </div>
                    </div>
                </template>

                <template v-else-if="tab === 'legal'">
                    <div class="space-y-3">
                        <div
                            v-for="doc in legal"
                            :key="doc.slug"
                            class="flex flex-col gap-2 border border-slate-200 px-3 py-2 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="font-jost-medium text-dark-blue-500">
                                    {{ doc.title }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ doc.slug }}
                                </p>
                            </div>
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    class="text-sm text-pink-700 hover:underline"
                                    @click="editLegal(doc)"
                                >
                                    Изменить
                                </button>
                                <button
                                    type="button"
                                    class="text-sm text-red-700 hover:underline"
                                    @click="removeLegal(doc.slug)"
                                >
                                    Удалить
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3 border-t border-slate-100 pt-4">
                        <h3 class="text-sm font-jost-medium text-dark-blue-500">
                            Редактор документа
                        </h3>
                        <input
                            v-model="legalForm.slug"
                            type="text"
                            placeholder="slug (privacy-policy)"
                            class="app-field"
                        />
                        <input
                            v-model="legalForm.type"
                            type="text"
                            placeholder="type"
                            class="app-field"
                        />
                        <input
                            v-model="legalForm.title"
                            type="text"
                            placeholder="Заголовок"
                            class="app-field"
                        />
                        <textarea
                            v-model="legalForm.body_html"
                            rows="8"
                            placeholder="HTML"
                            class="app-field font-mono text-sm"
                        />
                        <div class="app-actions">
                            <button
                                type="button"
                                class="app-btn-primary"
                                :disabled="saving"
                                @click="saveLegal"
                            >
                                Сохранить документ
                            </button>
                            <button
                                type="button"
                                class="app-btn-ghost"
                                @click="resetLegal"
                            >
                                Очистить
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
