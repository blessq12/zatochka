<script>
import { siteContentService } from "../../services/SiteContentService.js";

export default {
    name: "SiteContentPage",
        data() {
        return {
            form: null,
            loading: false,
            error: null,
            saved: false,
        };
    },
    async mounted() {
        this.form = await siteContentService.get();
    },
    methods: {
        async save() {
            this.loading = true;
            this.error = null;
            this.saved = false;
            try {
                this.form = await siteContentService.save(this.form);
                this.saved = true;
            } catch (e) {
                this.error = e.response?.data?.message || "Не удалось сохранить";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <div v-if="form" class="max-w-3xl bg-white border border-slate-200 p-6 space-y-6">
            <h1 class="text-xl font-jost-bold text-dark-blue-500">Контент сайта</h1>
            <div v-if="error" class="bg-red-50 text-red-700 px-4 py-3 text-sm">{{ error }}</div>
            <div v-if="saved" class="bg-green-50 text-green-700 px-4 py-3 text-sm">Сохранено</div>

            <section class="space-y-3">
                <h2 class="font-jost-bold">Компания</h2>
                <input v-model="form.company.owner_name" placeholder="Владелец" class="w-full border border-slate-300 px-3 py-2" />
                <input v-model="form.company.inn" placeholder="ИНН" class="w-full border border-slate-300 px-3 py-2" />
                <input v-model="form.company.ogrn" placeholder="ОГРН" class="w-full border border-slate-300 px-3 py-2" />
                <input v-model="form.company.legal_address" placeholder="Юр. адрес" class="w-full border border-slate-300 px-3 py-2" />
                <input v-model="form.company.actual_address" placeholder="Факт. адрес" class="w-full border border-slate-300 px-3 py-2" />
            </section>

            <section class="space-y-3">
                <h2 class="font-jost-bold">Контакты</h2>
                <input v-model="form.contacts.contact_person" placeholder="Контактное лицо" class="w-full border border-slate-300 px-3 py-2" />
                <input v-model="form.contacts.phone" placeholder="Телефон" class="w-full border border-slate-300 px-3 py-2" />
                <input v-model="form.contacts.email" placeholder="Email" class="w-full border border-slate-300 px-3 py-2" />
                <input v-model="form.contacts.address_main" placeholder="Адрес" class="w-full border border-slate-300 px-3 py-2" />
                <textarea v-model="form.contacts.entrance_directions" placeholder="Как пройти" class="w-full border border-slate-300 px-3 py-2" rows="2" />
            </section>

            <button type="button" class="bg-pink-500 text-white px-4 py-2 font-jost-bold text-sm" :disabled="loading" @click="save">
                Сохранить
            </button>
        </div>
</template>
