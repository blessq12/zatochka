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
    methods: {
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
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-jost-bold text-dark-blue-500">Контент сайта</h1>
            <button
                v-if="tab !== 'legal'"
                type="button"
                class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                :disabled="saving || loading"
                @click="saveCurrent"
            >
                {{ saving ? "Сохранение…" : "Сохранить раздел" }}
            </button>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="item in tabs"
                :key="item.id"
                type="button"
                class="border px-3 py-1.5 text-sm font-jost-medium"
                :class="
                    tab === item.id
                        ? 'border-pink-500 bg-pink-50 text-pink-600'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-pink-300'
                "
                @click="tab = item.id"
            >
                {{ item.label }}
            </button>
        </div>

        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        <p v-if="success" class="text-sm text-green-700">{{ success }}</p>
        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>

        <div v-else class="space-y-4 border border-slate-200 bg-white p-4">
            <template v-if="tab === 'company'">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block space-y-1 sm:col-span-2">
                        <span class="text-sm text-slate-600">Название</span>
                        <input v-model="company.name" type="text" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1 sm:col-span-2">
                        <span class="text-sm text-slate-600">Слоган</span>
                        <input v-model="company.tagline" type="text" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">Владелец</span>
                        <input v-model="company.owner_name" type="text" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">ИНН</span>
                        <input v-model="company.inn" type="text" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">ОГРН</span>
                        <input v-model="company.ogrn" type="text" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1 sm:col-span-2">
                        <span class="text-sm text-slate-600">Юр. адрес</span>
                        <input v-model="company.legal_address" type="text" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1 sm:col-span-2">
                        <span class="text-sm text-slate-600">Факт. адрес</span>
                        <input v-model="company.actual_address" type="text" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                </div>
            </template>

            <template v-else-if="tab === 'contacts'">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">Телефон</span>
                        <input v-model="contacts.phone" type="tel" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1">
                        <span class="text-sm text-slate-600">Email</span>
                        <input v-model="contacts.email" type="email" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1 sm:col-span-2">
                        <span class="text-sm text-slate-600">Контактное лицо</span>
                        <input v-model="contacts.contact_person" type="text" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1 sm:col-span-2">
                        <span class="text-sm text-slate-600">Адрес</span>
                        <input v-model="contacts.address.main" type="text" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1 sm:col-span-2">
                        <span class="text-sm text-slate-600">Как пройти</span>
                        <textarea v-model="contacts.address.directions" rows="3" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                    <label class="block space-y-1 sm:col-span-2">
                        <span class="text-sm text-slate-600">Email для соцблока</span>
                        <input v-model="contacts.social.email" type="email" class="w-full border border-slate-300 px-3 py-2" />
                    </label>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h2 class="font-jost-medium text-dark-blue-500">Соцссылки</h2>
                        <button type="button" class="text-sm text-pink-500" @click="addLink">+ ссылка</button>
                    </div>
                    <div v-for="(link, index) in contacts.social.links" :key="index" class="flex gap-2">
                        <input v-model="link.name" type="text" placeholder="Название" class="w-1/3 border border-slate-300 px-3 py-2" />
                        <input v-model="link.url" type="url" placeholder="URL" class="flex-1 border border-slate-300 px-3 py-2" />
                        <button type="button" class="text-sm text-slate-500" @click="removeLink(index)">✕</button>
                    </div>
                </div>
            </template>

            <template v-else-if="tab === 'schedule'">
                <div class="flex justify-end">
                    <button type="button" class="text-sm text-pink-500" @click="addDay">+ день</button>
                </div>
                <div v-for="(day, index) in scheduleDays" :key="index" class="flex gap-2">
                    <input v-model="day.label" type="text" placeholder="Пн–Пт" class="w-1/3 border border-slate-300 px-3 py-2" />
                    <input v-model="day.hours" type="text" placeholder="10:00–19:00" class="flex-1 border border-slate-300 px-3 py-2" />
                    <button type="button" class="text-sm text-slate-500" @click="removeDay(index)">✕</button>
                </div>
            </template>

            <template v-else-if="tab === 'faq'">
                <div class="flex justify-end">
                    <button type="button" class="text-sm text-pink-500" @click="addFaq">+ вопрос</button>
                </div>
                <div v-for="(item, index) in faqItems" :key="index" class="space-y-2 border border-slate-100 p-3">
                    <input v-model="item.question" type="text" placeholder="Вопрос" class="w-full border border-slate-300 px-3 py-2" />
                    <textarea v-model="item.answer_text" rows="3" placeholder="Ответ (строки с новой строки)" class="w-full border border-slate-300 px-3 py-2" />
                    <button type="button" class="text-sm text-slate-500" @click="removeFaq(index)">Удалить</button>
                </div>
            </template>

            <template v-else-if="tab === 'delivery'">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h2 class="font-jost-medium">Условия бесплатной доставки</h2>
                        <button type="button" class="text-sm text-pink-500" @click="addCondition">+ условие</button>
                    </div>
                    <div v-for="(_, index) in delivery.free_conditions" :key="'c'+index" class="flex gap-2">
                        <input v-model="delivery.free_conditions[index]" type="text" class="flex-1 border border-slate-300 px-3 py-2" />
                        <button type="button" class="text-sm text-slate-500" @click="removeCondition(index)">✕</button>
                    </div>
                </div>
                <div class="space-y-2 pt-4">
                    <div class="flex items-center justify-between">
                        <h2 class="font-jost-medium">Преимущества</h2>
                        <button type="button" class="text-sm text-pink-500" @click="addAdvantage">+ преимущество</button>
                    </div>
                    <div v-for="(row, index) in delivery.advantages" :key="'a'+index" class="space-y-2 border border-slate-100 p-3">
                        <input v-model="row.title" type="text" placeholder="Заголовок" class="w-full border border-slate-300 px-3 py-2" />
                        <textarea v-model="row.text" rows="2" placeholder="Текст" class="w-full border border-slate-300 px-3 py-2" />
                        <button type="button" class="text-sm text-slate-500" @click="removeAdvantage(index)">Удалить</button>
                    </div>
                </div>
            </template>

            <template v-else-if="tab === 'prices'">
                <div class="flex justify-end">
                    <button type="button" class="text-sm text-pink-500" @click="addPrice">+ позиция</button>
                </div>
                <div v-for="(item, index) in prices" :key="index" class="grid gap-2 border border-slate-100 p-3 sm:grid-cols-6">
                    <select v-model="item.category" class="border border-slate-300 px-2 py-2 sm:col-span-1">
                        <option value="sharpening">Заточка</option>
                        <option value="repair">Ремонт</option>
                    </select>
                    <input v-model="item.name" type="text" placeholder="Название" class="border border-slate-300 px-2 py-2 sm:col-span-2" />
                    <input v-model="item.description" type="text" placeholder="Описание" class="border border-slate-300 px-2 py-2 sm:col-span-1" />
                    <input v-model="item.price" type="text" placeholder="Цена" class="border border-slate-300 px-2 py-2" />
                    <div class="flex gap-2">
                        <select v-model="item.prefix" class="flex-1 border border-slate-300 px-2 py-2">
                            <option :value="null">—</option>
                            <option value="from">от</option>
                            <option value="to">до</option>
                        </select>
                        <button type="button" class="text-sm text-slate-500" @click="removePrice(index)">✕</button>
                    </div>
                </div>
            </template>

            <template v-else-if="tab === 'legal'">
                <div class="space-y-3">
                    <div v-for="doc in legal" :key="doc.slug" class="flex items-center justify-between border border-slate-100 px-3 py-2">
                        <div>
                            <p class="font-jost-medium text-dark-blue-500">{{ doc.title }}</p>
                            <p class="text-xs text-slate-500">{{ doc.slug }}</p>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" class="text-sm text-pink-500" @click="editLegal(doc)">Изменить</button>
                            <button type="button" class="text-sm text-slate-500" @click="removeLegal(doc.slug)">Удалить</button>
                        </div>
                    </div>
                </div>
                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <h2 class="font-jost-medium text-dark-blue-500">Редактор документа</h2>
                    <input v-model="legalForm.slug" type="text" placeholder="slug (privacy-policy)" class="w-full border border-slate-300 px-3 py-2" />
                    <input v-model="legalForm.type" type="text" placeholder="type" class="w-full border border-slate-300 px-3 py-2" />
                    <input v-model="legalForm.title" type="text" placeholder="Заголовок" class="w-full border border-slate-300 px-3 py-2" />
                    <textarea v-model="legalForm.body_html" rows="8" placeholder="HTML" class="w-full border border-slate-300 px-3 py-2 font-mono text-sm" />
                    <div class="flex gap-3">
                        <button
                            type="button"
                            class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                            :disabled="saving"
                            @click="saveLegal"
                        >
                            Сохранить документ
                        </button>
                        <button type="button" class="border border-slate-300 px-4 py-2 text-sm" @click="resetLegal">
                            Очистить
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
